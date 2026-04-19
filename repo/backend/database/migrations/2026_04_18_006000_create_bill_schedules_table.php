<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bill_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('source_type', ['enrollment', 'service', 'manual']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('schedule_type', ['one_time', 'recurring_monthly', 'supplemental', 'penalty']);
            $table->unsignedBigInteger('amount_cents');
            $table->foreignId('fee_category_id')->constrained('fee_categories')->restrictOnDelete();
            $table->date('start_on');
            $table->date('end_on')->nullable();
            $table->enum('status', ['active', 'paused', 'closed'])->default('active');
            $table->date('next_run_on')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_on']);
            $table->index(['user_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_schedules');
    }
};
