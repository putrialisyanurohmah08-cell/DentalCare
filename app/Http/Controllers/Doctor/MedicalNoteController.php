<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MedicalNote;
use App\Notifications\MedicalNoteReadyNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalNoteController extends Controller
{
    public function index(Request $request): View
    {
        return view('doctor.medical-notes.index', [
            'bookings' => Booking::query()
                ->where('doctor_id', $request->user()->id)
                ->whereIn('booking_status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
                ->with(['patient', 'service', 'medicalNote'])
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->paginate(10),
        ]);
    }

    public function create(Request $request, Booking $booking): View
    {
        abort_unless($booking->doctor_id === $request->user()->id, 403);
        abort_unless($booking->booking_status !== BookingStatus::PendingPayment && $booking->booking_status !== BookingStatus::Cancelled, 403);

        return view('doctor.medical-notes.form', [
            'booking' => $booking->loadMissing(['patient', 'service', 'medicalNote']),
            'medicalNote' => $booking->medicalNote,
        ]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->doctor_id === $request->user()->id, 403);
        abort_unless(
            $booking->booking_status !== BookingStatus::PendingPayment
            && $booking->booking_status !== BookingStatus::Cancelled,
            403
        );

        $validated = $request->validate([
            'diagnosis' => ['required', 'string'],
            'treatment' => ['required', 'string'],
            'prescription' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $medicalNote = MedicalNote::updateOrCreate(
            ['booking_id' => $booking->id],
            array_merge($validated, [
                'doctor_id' => $request->user()->id,
                'patient_id' => $booking->patient_id,
            ])
        );

        $booking->update([
            'booking_status' => BookingStatus::Completed,
        ]);

        $booking->patient->notify(new MedicalNoteReadyNotification($medicalNote->load('booking')));

        return redirect()
            ->route('doctor.medical-notes.index')
            ->with('success', 'Resume medis berhasil disimpan.');
    }
}
