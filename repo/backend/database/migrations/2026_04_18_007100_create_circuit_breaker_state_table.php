<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('circuit_breaker_state', function (Blueprint $table) {
            $table->id();
            $table->enum('mode', ['read_write', 'read_only'])->default('read_write');
            $table->timestamp('tripped_at')->nullable();
            $table->string('tripped_reason')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuit_breaker_state');
    }
};
