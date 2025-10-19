<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('trial_days')->default(14);
            $table->unsignedInteger('max_networks')->default(1);
            $table->unsignedInteger('daily_sync_limit')->nullable();
            $table->unsignedInteger('monthly_sync_limit')->nullable();
            $table->decimal('revenue_cap', 12, 2)->nullable();
            $table->unsignedInteger('orders_cap')->nullable();
            // Flexible sync window: unit (hour/day) and size (e.g., 1 hour, 1 day)
            $table->enum('sync_window_unit', ['minute', 'hour', 'day'])->default('day');
            $table->unsignedSmallInteger('sync_window_size')->default(1);
            // Optional allowed time range within a day for running sync (from/to time)
            $table->time('sync_allowed_from_time')->nullable();
            $table->time('sync_allowed_to_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};


