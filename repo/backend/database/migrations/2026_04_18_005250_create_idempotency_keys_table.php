<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100);
            $table->char('key_hash', 64);
            $table->char('request_fingerprint', 64);
            $table->unsignedSmallInteger('result_status');
            $table->json('result_body')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at');

            $table->unique(['scope', 'key_hash']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
