<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reservasi berhasil dibuat',
            'message' => 'Reservasi '.$this->booking->booking_code.' telah dibuat dan menunggu pembayaran.',
            'url' => route('history.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reservasi berhasil dibuat')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Reservasi '.$this->booking->booking_code.' berhasil dibuat dan saat ini menunggu pembayaran.')
            ->line('Silakan selesaikan pembayaran agar slot kunjungan Anda tetap aman.')
            ->action('Lihat riwayat reservasi', route('history.index'));
    }
}
