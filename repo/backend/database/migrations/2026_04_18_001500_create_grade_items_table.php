<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('max_score');
            $table->unsignedInteger('weight_bps')->default(0);
            $table->enum('state', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_items');
    }
};
