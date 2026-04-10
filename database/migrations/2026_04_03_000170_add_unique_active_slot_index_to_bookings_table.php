<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unique(
                ['doctor_id', 'booking_date', 'booking_time', 'IsDeleted'],
                'bookings_doctor_date_time_active_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('bookings_doctor_date_time_active_unique');
        });
    }
};
