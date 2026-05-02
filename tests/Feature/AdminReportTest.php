<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesClinicData;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use CreatesClinicData;
    use RefreshDatabase;

    public function test_admin_can_view_report_dashboard(): void
    {
        $admin = $this->createAdmin();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::Completed,
            'booking_date' => Carbon::parse('2026-03-20')->toDateString(),
        ]);

        $this->createPayment($booking, [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => Carbon::parse('2026-03-20 10:00:00'),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertSee('Laporan Klinik')
            ->assertSee('350.000');
    }
}
