<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->string('section_code');
            $table->unsignedInteger('capacity')->default(0);
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();

            $table->unique(['course_id', 'term_id', 'section_code'], 'sections_unique_code');
            $table->index(['term_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
