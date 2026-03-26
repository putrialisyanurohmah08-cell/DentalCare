<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $featuredServices = Service::query()->latest()->take(4)->get();
        $featuredDoctors = User::query()
            ->where('role', UserRole::Doctor->value)
            ->with(['doctorProfile', 'doctorSchedules'])
            ->take(4)
            ->get();

        return view('public.home', [
            'featuredServices' => $featuredServices,
            'featuredDoctors' => $featuredDoctors,
            'stats' => [
                'doctors' => User::query()->where('role', UserRole::Doctor->value)->count(),
                'services' => Service::query()->count(),
                'bookings' => Booking::query()->count(),
                'payments' => Payment::query()->where('payment_status', PaymentStatus::Paid->value)->count(),
            ],
        ]);
    }

    public function services(): View
    {
        return view('public.services', [
            'services' => Service::query()->latest()->paginate(9),
        ]);
    }

    public function doctors(): View
    {
        return view('public.doctors', [
            'doctors' => User::query()
                ->where('role', UserRole::Doctor->value)
                ->with(['doctorProfile', 'doctorSchedules'])
                ->paginate(9),
        ]);
    }
}
