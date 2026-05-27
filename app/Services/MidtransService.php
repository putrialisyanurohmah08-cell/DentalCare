<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Booking $booking, Payment $payment): array
    {
        if (! $this->isConfigured()) {
            return [
                'token' => 'sandbox-unconfigured-'.$payment->id,
                'redirect_url' => null,
                'response' => [
                    'mocked' => true,
                    'message' => 'Midtrans belum dikonfigurasi di environment.',
                ],
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->order_id,
                'gross_amount' => (int) round($payment->amount),
            ],
            'customer_details' => [
                'first_name' => $booking->patient->name,
                'email' => $booking->patient->email,
                'phone' => $booking->patient->phone,
            ],
            'item_details' => [
                [
                    'id' => 'service-'.$booking->service_id,
                    'price' => (int) round($payment->amount),
                    'quantity' => 1,
                    'name' => $booking->service_name,
                ],
            ],
            'callbacks' => [
                'finish' => route('payment.finish'),
                'unfinish' => route('payment.unfinish'),
                'error' => route('payment.error'),
            ],
        ];

        $transaction = Snap::createTransaction($payload);

        return [
            'token' => $transaction->token,
            'redirect_url' => $transaction->redirect_url,
            'response' => (array) $transaction,
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key')) && filled(config('midtrans.client_key'));
    }

    public function verifySignature(array $payload): bool
    {
        if (! $this->isConfigured()) {
            return app()->environment('testing');
        }

        $signature = hash(
            'sha512',
            ($payload['order_id'] ?? '')
            .($payload['status_code'] ?? '')
            .($payload['gross_amount'] ?? '')
            .config('midtrans.server_key')
        );

        return hash_equals($signature, $payload['signature_key'] ?? '');
    }

    public function fetchStatus(string $orderId): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        return (array) Transaction::status($orderId);
    }

    public function mapStatus(array $payload): PaymentStatus
    {
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? 'accept'));

        return match (true) {
            $transactionStatus === 'settlement' => PaymentStatus::Paid,
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => PaymentStatus::Paid,
            default => PaymentStatus::Failed,
        };
    }

    public function extractPaymentMethod(array $payload): ?string
    {
        if (! empty($payload['va_numbers'][0]['bank'])) {
            return strtoupper((string) $payload['va_numbers'][0]['bank']);
        }

        return $payload['store'] ?? $payload['payment_type'] ?? null;
    }
}
