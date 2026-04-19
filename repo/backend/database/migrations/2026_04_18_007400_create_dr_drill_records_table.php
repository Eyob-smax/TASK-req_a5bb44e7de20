<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dr_drill_records', function (Blueprint $table) {
            $table->id();
            $table->date('drill_date');
            $table->foreignId('operator_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('outcome', ['passed', 'failed', 'partial']);
            $table->text('notes');
            $table->timestamp('created_at')->useCurrent();

            $table->index('drill_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dr_drill_records');
    }
};
