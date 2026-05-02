<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\DoctorSchedule;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {
    }

    public function availableSlots(User $doctor, Carbon $date, ?Service $service = null): array
    {
        $schedule = $this->scheduleFor($doctor, $date);

        if (! $schedule) {
            return [];
        }

        $activeBookings = $this->activeBookingsQuery($doctor, $date)
            ->with('service:id,duration_minutes')
            ->get(['id', 'service_id', 'booking_time']);

        if ($activeBookings->count() >= $schedule->quota) {
            return [];
        }

        return $this->buildAvailableSlots($schedule, $activeBookings, $service?->duration_minutes);
    }

    public function createBooking(User $patient, array $validated): Booking
    {
        $doctor = User::query()->whereKey($validated['doctor_id'])->firstOrFail();
        $service = Service::query()->whereKey($validated['service_id'])->firstOrFail();
        $date = Carbon::parse($validated['booking_date']);

        if (! $doctor->isDoctor()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Dokter yang dipilih tidak valid.',
            ]);
        }

        try {
            return DB::transaction(function () use ($patient, $doctor, $service, $date, $validated): Booking {
                $schedule = $this->scheduleFor($doctor, $date, true);

                if (! $schedule) {
                    throw ValidationException::withMessages([
                        'booking_date' => 'Dokter belum memiliki jadwal pada tanggal yang dipilih.',
                    ]);
                }

                $activeBookings = $this->activeBookingsQuery($doctor, $date)
                    ->with('service:id,duration_minutes')
                    ->lockForUpdate()
                    ->get(['id', 'service_id', 'booking_time']);

                $availableSlots = $this->buildAvailableSlots($schedule, $activeBookings, $service->duration_minutes);

                if (! in_array($validated['booking_time'], $availableSlots, true)) {
                    throw ValidationException::withMessages([
                        'booking_time' => 'Slot waktu yang dipilih sudah tidak tersedia. Silakan pilih slot lain.',
                    ]);
                }

                $booking = Booking::create([
                    'booking_code' => 'DC-'.Str::upper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'service_id' => $service->id,
                    'booking_date' => $date->toDateString(),
                    'booking_time' => $validated['booking_time'],
                    'queue_number' => $activeBookings->count() + 1,
                    'booking_status' => BookingStatus::PendingPayment,
                    'service_name' => $service->name,
                    'service_price' => $service->price,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'order_id' => 'PAY-'.$booking->booking_code,
                    'amount' => $service->price,
                    'payment_status' => PaymentStatus::Pending,
                ]);

                $transaction = $this->midtransService->createTransaction(
                    $booking->loadMissing('patient'),
                    $payment
                );

                $payment->update([
                    'snap_token' => $transaction['token'],
                    'redirect_url' => $transaction['redirect_url'],
                    'raw_response' => $transaction['response'],
                ]);

                return $booking->load(['doctor.doctorProfile', 'patient', 'service', 'payment']);
            });
        } catch (QueryException $exception) {
            if ($this->isActiveSlotConflict($exception)) {
                throw ValidationException::withMessages([
                    'booking_time' => 'Slot waktu baru saja diambil pasien lain. Silakan pilih slot lain.',
                ]);
            }

            throw $exception;
        }
    }

    private function scheduleFor(User $doctor, Carbon $date, bool $lockForUpdate = false): ?DoctorSchedule
    {
        $query = $doctor->doctorSchedules()
            ->where('day_of_week', $date->dayOfWeekIso);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function activeBookingsQuery(User $doctor, Carbon $date): Builder
    {
        return Booking::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('booking_status', [
                BookingStatus::PendingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
            ]);
    }

    private function buildAvailableSlots(
        DoctorSchedule $schedule,
        Collection $activeBookings,
        ?int $requestedDurationMinutes = null,
    ): array {
        $slotMinutes = max(1, $schedule->slot_minutes);
        $requestedDurationMinutes = max($slotMinutes, $requestedDurationMinutes ?? $slotMinutes);
        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);
        $slots = [];
        $cursor = $scheduleStart->copy();

        while ($cursor->copy()->addMinutes($requestedDurationMinutes)->lessThanOrEqualTo($scheduleEnd)) {
            if (! $this->hasOverlappingBooking($cursor, $requestedDurationMinutes, $activeBookings, $slotMinutes)) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes($slotMinutes);
        }

        return $slots;
    }

    private function hasOverlappingBooking(
        Carbon $candidateStart,
        int $requestedDurationMinutes,
        Collection $activeBookings,
        int $fallbackDurationMinutes,
    ): bool {
        $candidateEnd = $candidateStart->copy()->addMinutes($requestedDurationMinutes);

        foreach ($activeBookings as $booking) {
            $bookingStart = Carbon::parse($booking->booking_time);
            $bookingDurationMinutes = max(
                $fallbackDurationMinutes,
                $booking->service?->duration_minutes ?? $fallbackDurationMinutes
            );
            $bookingEnd = $bookingStart->copy()->addMinutes($bookingDurationMinutes);

            if ($candidateStart->lt($bookingEnd) && $candidateEnd->gt($bookingStart)) {
                return true;
            }
        }

        return false;
    }

    private function isActiveSlotConflict(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'bookings_doctor_date_time_active_unique')
            || (in_array($exception->getCode(), ['23000', '23505'], true) && str_contains($message, 'bookings'));
    }
}
