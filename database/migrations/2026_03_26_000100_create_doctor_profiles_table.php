<?php

use App\Support\SchemaAuditColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('specialization');
            $table->string('license_number')->unique();
            $table->text('biography')->nullable();
            $table->unsignedTinyInteger('experience_years')->default(0);
            SchemaAuditColumns::add($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
