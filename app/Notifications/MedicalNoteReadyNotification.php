<?php

namespace App\Notifications;

use App\Models\MedicalNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MedicalNoteReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly MedicalNote $medicalNote,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Resume medis tersedia',
            'message' => 'Dokter telah mengunggah resume medis untuk reservasi '.$this->medicalNote->booking->booking_code.'.',
            'url' => route('history.medical-record', $this->medicalNote->booking),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Resume medis sudah tersedia')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Dokter telah mengunggah resume medis untuk reservasi '.$this->medicalNote->booking->booking_code.'.')
            ->action('Lihat resume medis', route('history.medical-record', $this->medicalNote->booking));
    }
}
