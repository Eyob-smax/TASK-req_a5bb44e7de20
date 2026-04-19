<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('method', ['cash', 'check', 'local_terminal', 'waiver']);
            $table->foreignId('operator_user_id')->constrained('users')->restrictOnDelete();
            $table->string('kiosk_id')->nullable();
            $table->unsignedBigInteger('amount_cents');
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->foreignId('idempotency_key_id')->nullable()->constrained('idempotency_keys')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
