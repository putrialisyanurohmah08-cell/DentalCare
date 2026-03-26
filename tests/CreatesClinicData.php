<?php

namespace Tests;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesClinicData
{
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Admin DentalCare',
            'email' => 'admin'.Str::random(5).'@example.com',
            'role' => UserRole::Admin,
        ], $attributes));
    }

    protected function createPatient(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Pasien Demo',
            'email' => 'patient'.Str::random(5).'@example.com',
            'role' => UserRole::Patient,
        ], $attributes));
    }

    protected function createDoctor(array $attributes = []): User
    {
        $doctor = User::factory()->create(array_merge([
            'name' => 'drg. Demo',
            'email' => 'doctor'.Str::random(5).'@example.com',
            'role' => UserRole::Doctor,
        ], $attributes));

        $doctor->doctorProfile()->create([
            'specialization' => 'Konservasi Gigi',
            'license_number' => 'SIP-'.Str::upper(Str::random(8)),
            'experience_years' => 5,
            'biography' => 'Profil dokter demo.',
        ]);

        return $doctor;
    }

    protected function createService(array $attributes = []): Service
    {
        return Service::create(array_merge([
            'name' => 'Scaling',
            'slug' => 'scaling-'.Str::lower(Str::random(5)),
            'description' => 'Layanan scaling demo.',
            'duration_minutes' => 45,
            'price' => 350000,
        ], $attributes));
    }

    protected function createSchedule(User $doctor, array $attributes = [])
    {
        return $doctor->doctorSchedules()->create(array_merge([
            'day_of_week' => now()->addDay()->dayOfWeekIso,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'quota' => 4,
            'slot_minutes' => 30,
        ], $attributes));
    }

    protected function createBooking(User $patient, User $doctor, Service $service, array $attributes = []): Booking
    {
        return Booking::create(array_merge([
            'booking_code' => 'DC-'.Str::upper(Str::random(8)),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'queue_number' => 1,
            'booking_status' => BookingStatus::Confirmed,
            'service_name' => $service->name,
            'service_price' => $service->price,
        ], $attributes));
    }

    protected function createPayment(Booking $booking, array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'booking_id' => $booking->id,
            'order_id' => 'PAY-'.$booking->booking_code,
            'amount' => $booking->service_price,
            'payment_status' => PaymentStatus::Pending,
        ], $attributes));
    }
}
