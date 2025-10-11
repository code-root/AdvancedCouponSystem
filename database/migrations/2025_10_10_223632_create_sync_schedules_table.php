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
        Schema::create('sync_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('network_ids');
            $table->enum('sync_type', ['campaigns', 'coupons', 'purchases', 'all'])->default('all');
            $table->integer('interval_minutes')->default(60);
            $table->integer('max_runs_per_day')->default(24);
            $table->integer('runs_today')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('date_range_type', ['today', 'yesterday', 'last_7_days', 'last_30_days', 'current_month', 'previous_month', 'custom'])->default('today');
            $table->date('custom_date_from')->nullable();
            $table->date('custom_date_to')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_schedules');
    }
};
