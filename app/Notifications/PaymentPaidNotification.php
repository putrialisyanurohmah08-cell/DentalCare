<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Payment $payment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pembayaran berhasil',
            'message' => 'Pembayaran untuk reservasi '.$this->payment->booking->booking_code.' sudah lunas.',
            'url' => route('history.invoice', $this->payment->booking),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembayaran reservasi berhasil')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Pembayaran untuk reservasi '.$this->payment->booking->booking_code.' sudah kami terima.')
            ->line('Invoice sekarang sudah tersedia untuk diunduh dari dashboard Anda.')
            ->action('Unduh invoice', route('history.invoice', $this->payment->booking));
    }
}
