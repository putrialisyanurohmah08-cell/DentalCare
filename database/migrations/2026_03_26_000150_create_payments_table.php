<?php

use App\Support\SchemaAuditColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 64)->nullable();
            $table->string('payment_type', 64)->nullable();
            $table->string('payment_status', 32)->default('pending');
            $table->string('snap_token')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('transaction_id', 128)->nullable();
            $table->json('raw_response')->nullable();
            $table->dateTime('paid_at')->nullable();
            SchemaAuditColumns::add($table);
            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
