<?php

namespace App\Notifications;

use App\Models\MedicalNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MedicalNoteReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly MedicalNote $medicalNote,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Resume medis tersedia',
            'message' => 'Dokter telah mengunggah resume medis untuk reservasi '.$this->medicalNote->booking->booking_code.'.',
            'url' => route('history.medical-record', $this->medicalNote->booking),
        ];
    }
}
