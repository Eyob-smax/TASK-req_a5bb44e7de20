<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('thread_type', ['announcement', 'discussion']);
            $table->boolean('qa_enabled')->default(false);
            $table->string('title');
            $table->text('body');
            $table->enum('state', ['visible', 'hidden', 'locked'])->default('visible');
            $table->timestamps();
            $table->timestamp('edited_at')->nullable();

            $table->index(['course_id', 'state', 'created_at']);
            $table->index(['author_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
