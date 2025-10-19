<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->index(); // error, warning, info, debug
            $table->string('message');
            $table->text('context')->nullable(); // JSON context data
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->string('trace')->nullable(); // Stack trace
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('request_id')->nullable(); // Unique request identifier
            $table->json('extra_data')->nullable(); // Additional data
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('occurrence_count')->default(1);
            $table->timestamp('last_occurred_at')->nullable();
            $table->timestamps();

            $table->index(['level', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['is_resolved', 'created_at']);
            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};