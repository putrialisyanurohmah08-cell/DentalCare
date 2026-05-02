<?php

use App\Support\SchemaAuditColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->longText('diagnosis');
            $table->longText('treatment');
            $table->longText('prescription')->nullable();
            $table->text('notes')->nullable();
            SchemaAuditColumns::add($table);
            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_notes');
    }
};
