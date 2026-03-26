<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Booking $booking,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reservasi berhasil dibuat',
            'message' => 'Reservasi '.$this->booking->booking_code.' telah dibuat dan menunggu pembayaran.',
            'url' => route('history.index'),
        ];
    }
}
