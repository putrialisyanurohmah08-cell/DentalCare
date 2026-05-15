<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends BaseModel
{
    use HasFactory;

    private const DISCOUNTED_SERVICES = [
        'paket-pasang-behel-atas-bawah-scaling-diskon-20' => [
            'original_price' => 4850000,
            'discount_percent' => 20,
        ],
    ];

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

    public function hasDiscount(): bool
    {
        return $this->discountPercent() !== null;
    }

    public function discountPercent(): ?int
    {
        return self::DISCOUNTED_SERVICES[$this->slug]['discount_percent'] ?? null;
    }

    public function originalPrice(): ?int
    {
        return self::DISCOUNTED_SERVICES[$this->slug]['original_price'] ?? null;
    }
}
