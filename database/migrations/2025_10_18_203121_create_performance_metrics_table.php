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
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('method', 10);
            $table->integer('status_code');
            $table->decimal('execution_time', 8, 4);
            $table->string('memory_usage');
            $table->string('peak_memory');
            $table->integer('query_count');
            $table->string('response_size');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('route')->nullable();
            $table->string('controller')->nullable();
            $table->timestamps();

            $table->index(['url', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index(['execution_time', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};