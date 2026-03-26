<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'order_id',
        'amount',
        'payment_method',
        'payment_type',
        'payment_status',
        'snap_token',
        'redirect_url',
        'transaction_id',
        'raw_response',
        'paid_at',
        'CompanyCode',
        'Status',
        'IsDeleted',
        'CreatedBy',
        'CreatedDate',
        'LastUpdatedBy',
        'LastUpdatedDate',
    ];

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'amount' => 'decimal:2',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
            'payment_status' => PaymentStatus::class,
        ]);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function statusLabel(): string
    {
        return $this->payment_status->label();
    }

    public function badgeClass(): string
    {
        return $this->payment_status->badgeClass();
    }
}
