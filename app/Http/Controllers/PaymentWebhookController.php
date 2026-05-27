<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Notifications\PaymentPaidNotification;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        abort_unless($midtransService->verifySignature($payload), 403);

        $payment = Payment::query()
            ->where('order_id', $payload['order_id'] ?? null)
            ->with('booking.patient')
            ->firstOrFail();

        $statusPayload = $midtransService->fetchStatus($payment->order_id);
        $sourcePayload = array_filter(array_merge($payload, $statusPayload));
        $status = $midtransService->mapStatus($sourcePayload);

        $payment->update([
            'payment_method' => $midtransService->extractPaymentMethod($sourcePayload),
            'payment_type' => $sourcePayload['payment_type'] ?? $payment->payment_type,
            'payment_status' => $status,
            'transaction_id' => $sourcePayload['transaction_id'] ?? $payment->transaction_id,
            'raw_response' => $sourcePayload,
            'paid_at' => $status === PaymentStatus::Paid ? now() : $payment->paid_at,
        ]);

        $rawStatus = $sourcePayload['transaction_status'] ?? null;
        $bookingStatus = match (true) {
            $status === PaymentStatus::Paid => BookingStatus::Confirmed,
            in_array($rawStatus, ['expire', 'cancel', 'deny']) => BookingStatus::Cancelled,
            default => BookingStatus::PendingPayment,
        };

        $payment->booking->update([
            'booking_status' => $bookingStatus,
        ]);

        if ($status === PaymentStatus::Paid) {
            $payment->booking->patient->notify(new PaymentPaidNotification($payment->fresh('booking')));
        }

        return response()->json(['received' => true]);
    }
}
