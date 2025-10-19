<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sync_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('period', ['daily', 'monthly']);
            $table->dateTime('window_start');
            $table->dateTime('window_end');
            $table->unsignedInteger('sync_count')->default(0);
            $table->decimal('revenue_sum', 12, 2)->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'period', 'window_start', 'window_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_usages');
    }
};




