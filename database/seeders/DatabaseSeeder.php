<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\MedicalNote;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate([
            'email' => 'admin@dentalcare.test',
        ], [
            'name' => 'Admin DentalCare',
            'phone' => '081234567890',
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $patient = User::query()->updateOrCreate([
            'email' => 'patient@dentalcare.test',
        ], [
            'name' => 'Putri Alisha',
            'phone' => '081298765432',
            'role' => UserRole::Patient,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $doctorOne = User::query()->updateOrCreate([
            'email' => 'dr.aji@dentalcare.test',
        ], [
            'name' => 'drg. Aji Pratama',
            'phone' => '081200000111',
            'role' => UserRole::Doctor,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $doctorTwo = User::query()->updateOrCreate([
            'email' => 'dr.salsa@dentalcare.test',
        ], [
            'name' => 'drg. Salsa Maharani',
            'phone' => '081200000222',
            'role' => UserRole::Doctor,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $doctorThree = User::query()->updateOrCreate([
            'email' => 'dr.rizky@dentalcare.test',
        ], [
            'name' => 'drg. Rizky Hanif',
            'phone' => '081200000333',
            'role' => UserRole::Doctor,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $doctorOne->doctorProfile()->updateOrCreate(
            ['user_id' => $doctorOne->id],
            [
                'specialization' => 'Konservasi Gigi',
                'license_number' => 'SIP-DC-001',
                'experience_years' => 8,
                'biography' => 'Fokus pada tambal gigi estetik dan perawatan akar dengan pendekatan ramah pasien.',
            ]
        );

        $doctorTwo->doctorProfile()->updateOrCreate(
            ['user_id' => $doctorTwo->id],
            [
                'specialization' => 'Ortodonti',
                'license_number' => 'SIP-DC-002',
                'experience_years' => 6,
                'biography' => 'Menangani konsultasi behel dan perawatan susunan gigi untuk remaja maupun dewasa.',
            ]
        );

        $doctorThree->doctorProfile()->updateOrCreate(
            ['user_id' => $doctorThree->id],
            [
                'specialization' => 'Bedah Mulut',
                'license_number' => 'SIP-DC-003',
                'experience_years' => 10,
                'biography' => 'Berpengalaman dalam tindakan cabut gigi bungsu dan prosedur bedah minor.',
            ]
        );

        foreach ([$doctorOne, $doctorTwo, $doctorThree] as $doctor) {
            foreach ([
                [1, '09:00', '15:00', 10],
                [3, '09:00', '15:00', 10],
                [5, '10:00', '16:00', 8],
            ] as [$day, $start, $end, $quota]) {
                $doctor->doctorSchedules()->updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $day],
                    [
                        'start_time' => $start,
                        'end_time' => $end,
                        'quota' => $quota,
                        'slot_minutes' => config('clinic.slot_minutes'),
                    ]
                );
            }
        }

        $services = collect([
            ['name' => 'Scaling', 'description' => 'Pembersihan karang gigi dengan evaluasi awal kondisi gusi.', 'duration_minutes' => 45, 'price' => 350000],
            ['name' => 'Cabut Gigi', 'description' => 'Tindakan pencabutan gigi dengan anestesi lokal dan observasi pasca tindakan.', 'duration_minutes' => 60, 'price' => 500000],
            ['name' => 'Tambal Estetik', 'description' => 'Perawatan gigi berlubang menggunakan bahan komposit warna gigi.', 'duration_minutes' => 50, 'price' => 425000],
            ['name' => 'Konsultasi Ortodonti', 'description' => 'Pemeriksaan susunan gigi dan rekomendasi perawatan behel.', 'duration_minutes' => 40, 'price' => 250000],
            ['name' => 'Pasang Behel Rahang Atas', 'description' => 'Pemasangan behel untuk rahang atas, termasuk pemeriksaan awal dan penyesuaian kawat.', 'duration_minutes' => 90, 'price' => 2500000],
            ['name' => 'Pasang Behel Rahang Bawah', 'description' => 'Pemasangan behel untuk rahang bawah, termasuk pemeriksaan awal dan penyesuaian kawat.', 'duration_minutes' => 90, 'price' => 2500000],
            ['name' => 'Paket Pasang Behel Atas Bawah + Scaling Diskon 20%', 'description' => 'Paket pasang behel rahang atas dan bawah ditambah scaling. Harga normal Rp 4.850.000, diskon 20% menjadi Rp 3.880.000.', 'duration_minutes' => 150, 'price' => 3880000],
        ])->map(fn (array $service) => Service::query()->updateOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($service['name'])],
            $service
        ));

        $completedBooking = Booking::query()->updateOrCreate([
            'booking_code' => 'DC-DEMO001',
        ], [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorOne->id,
            'service_id' => $services[0]->id,
            'booking_date' => Carbon::now()->subDays(5)->toDateString(),
            'booking_time' => '10:00',
            'queue_number' => 1,
            'booking_status' => BookingStatus::Completed,
            'service_name' => $services[0]->name,
            'service_price' => $services[0]->price,
            'notes' => 'Ingin membersihkan karang gigi dan cek sensitivitas.',
        ]);

        $confirmedBooking = Booking::query()->updateOrCreate([
            'booking_code' => 'DC-DEMO002',
        ], [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorTwo->id,
            'service_id' => $services[3]->id,
            'booking_date' => Carbon::now()->addDays(2)->toDateString(),
            'booking_time' => '09:30',
            'queue_number' => 2,
            'booking_status' => BookingStatus::Confirmed,
            'service_name' => $services[3]->name,
            'service_price' => $services[3]->price,
            'notes' => 'Konsultasi posisi gigi depan.',
        ]);

        $pendingBooking = Booking::query()->updateOrCreate([
            'booking_code' => 'DC-DEMO003',
        ], [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorThree->id,
            'service_id' => $services[1]->id,
            'booking_date' => Carbon::now()->addDays(4)->toDateString(),
            'booking_time' => '10:30',
            'queue_number' => 3,
            'booking_status' => BookingStatus::PendingPayment,
            'service_name' => $services[1]->name,
            'service_price' => $services[1]->price,
            'notes' => 'Keluhan gigi bungsu.',
        ]);

        Payment::query()->updateOrCreate([
            'order_id' => 'PAY-'.$completedBooking->booking_code,
        ], [
            'booking_id' => $completedBooking->id,
            'amount' => $completedBooking->service_price,
            'payment_method' => 'BCA VA',
            'payment_type' => 'bank_transfer',
            'payment_status' => PaymentStatus::Paid,
            'snap_token' => 'demo-paid-token',
            'transaction_id' => 'txn-demo-paid',
            'paid_at' => Carbon::now()->subDays(5),
            'raw_response' => ['seeded' => true, 'status' => 'paid'],
        ]);

        Payment::query()->updateOrCreate([
            'order_id' => 'PAY-'.$confirmedBooking->booking_code,
        ], [
            'booking_id' => $confirmedBooking->id,
            'amount' => $confirmedBooking->service_price,
            'payment_method' => 'GoPay',
            'payment_type' => 'gopay',
            'payment_status' => PaymentStatus::Paid,
            'snap_token' => 'demo-confirmed-token',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/demo-confirmed',
            'transaction_id' => 'txn-demo-confirmed',
            'paid_at' => Carbon::now()->subDay(),
            'raw_response' => ['seeded' => true, 'status' => 'paid'],
        ]);

        Payment::query()->updateOrCreate([
            'order_id' => 'PAY-'.$pendingBooking->booking_code,
        ], [
            'booking_id' => $pendingBooking->id,
            'amount' => $pendingBooking->service_price,
            'payment_status' => PaymentStatus::Pending,
            'snap_token' => 'demo-pending-token',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/demo-pending',
            'raw_response' => ['seeded' => true, 'status' => 'pending'],
        ]);

        MedicalNote::query()->updateOrCreate([
            'booking_id' => $completedBooking->id,
        ], [
            'doctor_id' => $doctorOne->id,
            'patient_id' => $patient->id,
            'diagnosis' => 'Karang gigi ringan dengan sensitivitas pada gigi depan bawah.',
            'treatment' => 'Dilakukan scaling ultrasonik dan edukasi teknik menyikat gigi.',
            'prescription' => 'Pasta gigi untuk gigi sensitif, digunakan dua kali sehari.',
            'notes' => 'Kontrol ulang bila sensitivitas tidak membaik dalam 2 minggu.',
        ]);
    }
}
