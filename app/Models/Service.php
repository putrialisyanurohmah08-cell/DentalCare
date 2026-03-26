<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_minutes',
        'price',
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
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
        ]);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
