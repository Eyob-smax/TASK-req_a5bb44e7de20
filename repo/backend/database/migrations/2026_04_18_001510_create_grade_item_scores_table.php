<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_item_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_item_id')->constrained('grade_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['grade_item_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_item_scores');
    }
};
