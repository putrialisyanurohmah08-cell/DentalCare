<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentPaidNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Payment $payment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pembayaran berhasil',
            'message' => 'Pembayaran untuk reservasi '.$this->payment->booking->booking_code.' sudah lunas.',
            'url' => route('history.invoice', $this->payment->booking),
        ];
    }
}
