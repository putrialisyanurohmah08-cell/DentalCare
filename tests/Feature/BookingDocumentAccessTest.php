<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesClinicData;
use Tests\TestCase;

class BookingDocumentAccessTest extends TestCase
{
    use CreatesClinicData;
    use RefreshDatabase;

    public function test_admin_can_download_paid_invoice(): void
    {
        $admin = $this->createAdmin();
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::Confirmed,
        ]);

        $this->createPayment($booking, [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('history.invoice', $booking))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_patient_cannot_download_invoice_before_payment_is_paid(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::PendingPayment,
        ]);

        $this->createPayment($booking, [
            'payment_status' => PaymentStatus::Pending,
        ]);

        $this->actingAs($patient)
            ->get(route('history.invoice', $booking))
            ->assertNotFound();
    }
}
