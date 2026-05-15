<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(Request $request, BookingService $bookingService): View
    {
        $featuredServices = Service::query()->latest()->take(4)->get();
        $featuredDoctors = User::query()
            ->where('role', UserRole::Doctor->value)
            ->with(['doctorProfile', 'doctorSchedules'])
            ->take(4)
            ->get();
        $services = Service::query()->orderBy('name')->get();
        $selectedDoctor = null;
        $selectedService = null;
        $selectedDate = $request->string('booking_date')->toString();
        $availableSlots = [];
        $slotSearchRequested = $request->boolean('check_slots');

        if ($request->filled('doctor_id')) {
            $selectedDoctor = User::query()
                ->where('role', UserRole::Doctor->value)
                ->whereKey($request->integer('doctor_id'))
                ->with('doctorSchedules')
                ->first();
        }

        if ($request->filled('service_id')) {
            $selectedService = $services->firstWhere('id', $request->integer('service_id'));
        }

        if ($slotSearchRequested && $selectedDoctor && $selectedService && $selectedDate) {
            $availableSlots = $bookingService->availableSlots(
                $selectedDoctor,
                Carbon::parse($selectedDate),
                $selectedService
            );
        }

        return view('public.home', [
            'featuredServices' => $featuredServices,
            'featuredDoctors' => $featuredDoctors,
            'doctors' => User::query()
                ->where('role', UserRole::Doctor->value)
                ->with(['doctorProfile', 'doctorSchedules'])
                ->orderBy('name')
                ->get(),
            'services' => $services,
            'selectedDoctor' => $selectedDoctor,
            'selectedService' => $selectedService,
            'selectedDate' => $selectedDate,
            'availableSlots' => $availableSlots,
            'slotSearchRequested' => $slotSearchRequested,
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

    public function slots(Request $request, BookingService $bookingService): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', UserRole::Doctor->value),
            ],
            'service_id' => ['required', Rule::exists('services', 'id')],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $doctor = User::query()
            ->where('role', UserRole::Doctor->value)
            ->whereKey($validated['doctor_id'])
            ->firstOrFail();
        $service = Service::query()->whereKey($validated['service_id'])->firstOrFail();

        return response()->json([
            'slots' => $bookingService->availableSlots(
                $doctor,
                Carbon::parse($validated['booking_date']),
                $service
            ),
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
