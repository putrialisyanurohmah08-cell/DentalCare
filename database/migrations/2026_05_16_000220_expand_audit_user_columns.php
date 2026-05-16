<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tables = [
        'users',
        'doctor_profiles',
        'doctor_schedules',
        'services',
        'bookings',
        'medical_notes',
        'payments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('CreatedBy')->change();
                $table->string('LastUpdatedBy')->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('CreatedBy', 32)->change();
                $table->string('LastUpdatedBy', 32)->change();
            });
        }
    }
};
