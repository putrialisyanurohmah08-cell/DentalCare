<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired
                            {--hours=24 : Hours after which unpaid bookings are cancelled}';

    protected $description = 'Cancel bookings that have been pending payment for longer than the configured threshold';

    public function handle(MidtransService $midtransService): int
    {
        $hours = (int) $this->option('hours');

        $expiredBookings = Booking::query()
            ->where('booking_status', BookingStatus::PendingPayment->value)
            ->where('CreatedDate', '<', now()->subHours($hours))
            ->with('payment')
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('Tidak ada booking kedaluwarsa yang perlu dibatalkan.');

            return self::SUCCESS;
        }

        $cancelled = 0;
        $synced = 0;

        foreach ($expiredBookings as $booking) {
            $payment = $booking->payment;

            // If Midtrans is configured, try to fetch the real status first
            // in case the patient actually paid but webhook didn't arrive
            if ($payment && $midtransService->isConfigured()) {
                try {
                    $statusPayload = $midtransService->fetchStatus($payment->order_id);

                    if (! empty($statusPayload)) {
                        $status = $midtransService->mapStatus($statusPayload);

                        if ($status === PaymentStatus::Paid) {
                            $payment->update([
                                'payment_method' => $midtransService->extractPaymentMethod($statusPayload),
                                'payment_type'   => $statusPayload['payment_type'] ?? $payment->payment_type,
                                'payment_status' => PaymentStatus::Paid,
                                'transaction_id' => $statusPayload['transaction_id'] ?? $payment->transaction_id,
                                'raw_response'   => $statusPayload,
                                'paid_at'        => now(),
                            ]);

                            $booking->update(['booking_status' => BookingStatus::Confirmed]);

                            $synced++;
                            $this->line("  ✓ {$booking->booking_code} — ternyata sudah dibayar, status disinkronkan.");

                            continue;
                        }
                    }
                } catch (Throwable $e) {
                    Log::warning('Failed to check Midtrans status during auto-cancel.', [
                        'booking_id' => $booking->id,
                        'exception'  => $e->getMessage(),
                    ]);
                }
            }

            // Cancel the booking
            $booking->update(['booking_status' => BookingStatus::Cancelled]);

            if ($payment && $payment->payment_status === PaymentStatus::Pending) {
                $payment->update(['payment_status' => PaymentStatus::Expired]);
            }

            $cancelled++;
            $this->line("  ✗ {$booking->booking_code} — dibatalkan (melebihi {$hours} jam tanpa pembayaran).");
        }

        $this->info("Selesai. Dibatalkan: {$cancelled}, Disinkronkan: {$synced}.");
        Log::info("CancelExpiredBookings: cancelled={$cancelled}, synced={$synced}");

        return self::SUCCESS;
    }
}
