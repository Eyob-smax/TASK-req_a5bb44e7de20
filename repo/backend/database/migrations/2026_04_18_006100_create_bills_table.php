<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bill_schedule_id')->nullable()->constrained('bill_schedules')->nullOnDelete();
            $table->enum('type', ['initial', 'recurring', 'supplemental', 'penalty']);
            $table->unsignedBigInteger('subtotal_cents')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);
            $table->unsignedBigInteger('paid_cents')->default(0);
            $table->unsignedBigInteger('refunded_cents')->default(0);
            $table->enum('status', ['open', 'partial', 'paid', 'void', 'past_due'])->default('open');
            $table->date('issued_on');
            $table->date('due_on');
            $table->dateTime('past_due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
