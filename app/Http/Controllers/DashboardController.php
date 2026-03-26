<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::Admin) {
            return redirect()->route('admin.reports.index');
        }

        if ($user->role === UserRole::Doctor) {
            return redirect()->route('doctor.dashboard');
        }

        $bookings = Booking::query()
            ->where('patient_id', $user->id)
            ->with(['doctor.doctorProfile', 'payment'])
            ->latest('booking_date')
            ->take(5)
            ->get();

        return view('dashboard', [
            'bookings' => $bookings,
            'stats' => [
                'total_bookings' => Booking::query()->where('patient_id', $user->id)->count(),
                'pending_payment' => Booking::query()
                    ->where('patient_id', $user->id)
                    ->where('booking_status', BookingStatus::PendingPayment->value)
                    ->count(),
                'confirmed' => Booking::query()
                    ->where('patient_id', $user->id)
                    ->where('booking_status', BookingStatus::Confirmed->value)
                    ->count(),
                'paid' => Booking::query()
                    ->where('patient_id', $user->id)
                    ->whereHas('payment', fn ($query) => $query->where('payment_status', PaymentStatus::Paid->value))
                    ->count(),
            ],
        ]);
    }
}
