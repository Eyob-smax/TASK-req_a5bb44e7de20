<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('entry_type', ['charge', 'payment', 'refund', 'reversal', 'penalty', 'tax_adjustment']);
            $table->bigInteger('amount_cents');
            $table->string('description');
            $table->foreignId('reference_entry_id')->nullable()->constrained('ledger_entries')->nullOnDelete();
            $table->uuid('correlation_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['entry_type', 'created_at']);
            $table->index('reference_entry_id');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
