<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'patient_id',
        'doctor_id',
        'service_id',
        'booking_date',
        'booking_time',
        'queue_number',
        'booking_status',
        'service_name',
        'service_price',
        'notes',
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
            'booking_date' => 'date',
            'service_price' => 'decimal:2',
            'queue_number' => 'integer',
            'booking_status' => BookingStatus::class,
        ]);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function medicalNote(): HasOne
    {
        return $this->hasOne(MedicalNote::class);
    }

    public function statusLabel(): string
    {
        return $this->booking_status->label();
    }

    public function badgeClass(): string
    {
        return $this->booking_status->badgeClass();
    }

    public function scheduleLabel(): string
    {
        return $this->booking_date->translatedFormat('d F Y').' • '.Carbon::parse($this->booking_time)->format('H:i');
    }
}
