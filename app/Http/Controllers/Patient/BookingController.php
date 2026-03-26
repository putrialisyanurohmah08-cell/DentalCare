<?php

namespace App\Http\Controllers\Patient;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Services\BookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, BookingService $bookingService): View
    {
        $selectedDoctor = null;
        $selectedDate = $request->string('booking_date')->toString();
        $availableSlots = [];

        if ($request->filled('doctor_id')) {
            $selectedDoctor = User::query()
                ->where('role', UserRole::Doctor->value)
                ->whereKey($request->integer('doctor_id'))
                ->with('doctorSchedules')
                ->first();
        }

        if ($selectedDoctor && $selectedDate) {
            $availableSlots = $bookingService->availableSlots($selectedDoctor, Carbon::parse($selectedDate));
        }

        return view('patient.bookings.create', [
            'doctors' => User::query()
                ->where('role', UserRole::Doctor->value)
                ->with(['doctorProfile', 'doctorSchedules'])
                ->orderBy('name')
                ->get(),
            'services' => Service::query()->orderBy('name')->get(),
            'selectedDoctor' => $selectedDoctor,
            'selectedDate' => $selectedDate,
            'availableSlots' => $availableSlots,
        ]);
    }

    public function store(Request $request, BookingService $bookingService): RedirectResponse
    {
        abort_unless($request->user()?->isPatient(), 403);

        $validated = $request->validate([
            'doctor_id' => ['required', Rule::exists('users', 'id')],
            'service_id' => ['required', Rule::exists('services', 'id')],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $bookingService->createBooking($request->user(), $validated);

        $request->user()->notify(new BookingCreatedNotification($booking));

        return redirect()
            ->route('history.index')
            ->with('success', 'Reservasi berhasil dibuat. Silakan lanjutkan pembayaran untuk mengamankan slot Anda.');
    }

    public function history(Request $request): View
    {
        return view('patient.history.index', [
            'bookings' => Booking::query()
                ->where('patient_id', $request->user()->id)
                ->with(['doctor.doctorProfile', 'service', 'payment', 'medicalNote'])
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->paginate(10),
            'pendingStatus' => BookingStatus::PendingPayment,
        ]);
    }

    public function invoice(Request $request, Booking $booking)
    {
        abort_unless(
            $request->user()->isAdmin() || $booking->patient_id === $request->user()->id,
            403
        );

        $booking->loadMissing(['doctor.doctorProfile', 'patient', 'payment']);

        abort_unless($booking->payment !== null, 404);

        return Pdf::loadView('pdf.invoice', [
            'booking' => $booking,
        ])->setPaper('a4')->download('invoice-'.$booking->booking_code.'.pdf');
    }

    public function medicalRecord(Request $request, Booking $booking)
    {
        abort_unless(
            $request->user()->isAdmin()
            || $booking->patient_id === $request->user()->id
            || $booking->doctor_id === $request->user()->id,
            403
        );

        $booking->loadMissing(['doctor.doctorProfile', 'patient', 'medicalNote']);

        abort_unless($booking->medicalNote !== null, 404);

        return Pdf::loadView('pdf.medical-record', [
            'booking' => $booking,
            'medicalNote' => $booking->medicalNote,
        ])->setPaper('a4')->download('resume-medis-'.$booking->booking_code.'.pdf');
    }
}
