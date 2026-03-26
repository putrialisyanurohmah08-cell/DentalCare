<?php

use App\Support\SchemaAuditColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->unsignedInteger('queue_number');
            $table->string('booking_status', 32)->default('pending_payment');
            $table->string('service_name');
            $table->decimal('service_price', 12, 2);
            $table->text('notes')->nullable();
            SchemaAuditColumns::add($table);
            $table->index(['doctor_id', 'booking_date']);
            $table->index(['patient_id', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
