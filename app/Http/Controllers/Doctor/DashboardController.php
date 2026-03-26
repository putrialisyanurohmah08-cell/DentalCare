<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $request->user();

        $todayBookings = Booking::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('booking_date', today())
            ->with(['patient', 'service', 'payment', 'medicalNote'])
            ->orderBy('booking_time')
            ->get();

        return view('doctor.dashboard', [
            'todayBookings' => $todayBookings,
            'stats' => [
                'today' => $todayBookings->count(),
                'confirmed' => $todayBookings->where('booking_status', BookingStatus::Confirmed)->count(),
                'completed' => $todayBookings->where('booking_status', BookingStatus::Completed)->count(),
                'revenue' => Payment::query()
                    ->whereHas('booking', fn ($query) => $query->where('doctor_id', $doctor->id))
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->sum('amount'),
            ],
        ]);
    }
}
