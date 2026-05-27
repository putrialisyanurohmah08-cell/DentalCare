<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Notifications\PaymentPaidNotification;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\CreatesClinicData;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use CreatesClinicData;
    use RefreshDatabase;

    public function test_midtrans_callback_updates_payment_and_booking_status(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();
        $booking = $this->createBooking($patient, $doctor, $service, [
            'booking_status' => BookingStatus::PendingPayment,
        ]);
        $payment = $this->createPayment($booking, [
            'payment_status' => PaymentStatus::Failed,
        ]);

        $midtrans = Mockery::mock(MidtransService::class);
        $midtrans->shouldReceive('verifySignature')->once()->andReturn(true);
        $midtrans->shouldReceive('fetchStatus')->once()->andReturn([
            'transaction_status' => 'settlement',
            'transaction_id' => 'txn-paid-001',
            'payment_type' => 'bank_transfer',
        ]);
        $midtrans->shouldReceive('mapStatus')->once()->andReturn(PaymentStatus::Paid);
        $midtrans->shouldReceive('extractPaymentMethod')->once()->andReturn('BCA');

        $this->app->instance(MidtransService::class, $midtrans);

        $this->postJson(route('payments.callback'), [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'gross_amount' => '350000.00',
            'signature_key' => 'test-signature',
        ])->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_method' => 'BCA',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => BookingStatus::Confirmed->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => PaymentPaidNotification::class,
            'notifiable_id' => $patient->id,
        ]);
    }
}
