<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('resource_type', ['facility', 'registrar_meeting', 'generic']);
            $table->string('resource_ref')->nullable();
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->enum('status', ['scheduled', 'rescheduled', 'canceled', 'completed'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['owner_user_id', 'scheduled_start']);
            $table->index(['status', 'scheduled_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
