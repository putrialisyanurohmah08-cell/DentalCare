<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Berhasil',
            self::Failed => 'Gagal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Failed => 'danger',
        };
    }
}
