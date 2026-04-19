<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reconciliation_flags', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['refund', 'manual_adjustment', 'ledger_mismatch']);
            $table->unsignedBigInteger('source_id');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();

            $table->index(['status', 'opened_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_flags');
    }
};
