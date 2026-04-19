<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diagnostic_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiated_by')->constrained('users')->restrictOnDelete();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->char('checksum_sha256', 64)->nullable();
            $table->string('encryption_key_id');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_exports');
    }
};
