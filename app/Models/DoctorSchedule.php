<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'quota',
        'slot_minutes',
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
            'day_of_week' => 'integer',
            'quota' => 'integer',
            'slot_minutes' => 'integer',
        ]);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public static function dayOptions(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    public function dayLabel(): string
    {
        return self::dayOptions()[$this->day_of_week] ?? 'Tidak diketahui';
    }

    public function formattedTimeRange(): string
    {
        return Carbon::parse($this->start_time)->format('H:i')
            .' - '.
            Carbon::parse($this->end_time)->format('H:i');
    }
}
