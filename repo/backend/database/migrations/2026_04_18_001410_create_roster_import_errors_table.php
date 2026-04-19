<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roster_import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_import_id')->constrained('roster_imports')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('error_code');
            $table->text('message');
            $table->json('raw_row')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('roster_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_import_errors');
    }
};
