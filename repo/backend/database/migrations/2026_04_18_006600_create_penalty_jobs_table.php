<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penalty_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedBigInteger('amount_cents');
            $table->enum('status', ['pending', 'applied', 'skipped'])->default('pending');
            $table->char('idempotency_key', 64)->unique();
            $table->timestamps();

            $table->index(['status', 'applied_at']);
            $table->index('bill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_jobs');
    }
};
