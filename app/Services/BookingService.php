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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {
    }

    public function availableSlots(User $doctor, Carbon $date): array
    {
        $schedule = $doctor->doctorSchedules()
            ->where('day_of_week', $date->dayOfWeekIso)
            ->first();

        if (! $schedule) {
            return [];
        }

        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $slots = [];

        while ($start->copy()->addMinutes($schedule->slot_minutes)->lessThanOrEqualTo($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($schedule->slot_minutes);
        }

        $activeBookings = Booking::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('booking_status', [
                BookingStatus::PendingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
            ])
            ->get(['booking_time']);

        if ($activeBookings->count() >= $schedule->quota) {
            return [];
        }

        $bookedSlots = $activeBookings
            ->pluck('booking_time')
            ->map(fn (string $time) => Carbon::parse($time)->format('H:i'))
            ->all();

        return array_values(array_diff($slots, $bookedSlots));
    }

    public function nextQueueNumber(User $doctor, Carbon $date): int
    {
        return Booking::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('booking_date', $date->toDateString())
            ->whereIn('booking_status', [
                BookingStatus::PendingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
            ])
            ->count() + 1;
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

        $availableSlots = $this->availableSlots($doctor, $date);

        if (! in_array($validated['booking_time'], $availableSlots, true)) {
            throw ValidationException::withMessages([
                'booking_time' => 'Slot waktu yang dipilih sudah tidak tersedia. Silakan pilih slot lain.',
            ]);
        }

        return DB::transaction(function () use ($patient, $doctor, $service, $date, $validated): Booking {
            $booking = Booking::create([
                'booking_code' => 'DC-'.Str::upper(Str::random(8)),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'booking_date' => $date->toDateString(),
                'booking_time' => $validated['booking_time'],
                'queue_number' => $this->nextQueueNumber($doctor, $date),
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
    }
}
