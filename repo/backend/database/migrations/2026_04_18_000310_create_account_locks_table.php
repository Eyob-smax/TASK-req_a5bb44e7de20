<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamp('locked_at');
            $table->timestamp('unlock_at');
            $table->string('reason');
            $table->timestamps();

            $table->index('unlock_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_locks');
    }
};
