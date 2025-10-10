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
        Schema::create('network_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('data_type'); // 'summary', 'campaigns', 'coupons', 'purchases', etc.
            $table->json('data'); // Store the actual data from network
            $table->date('data_date');
            $table->timestamp('synced_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['network_id', 'user_id', 'data_date']);
            $table->index(['data_type', 'data_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_data');
    }
};
