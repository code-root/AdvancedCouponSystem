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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('network_id')->constrained('networks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_id')->nullable();
            $table->string('network_order_id')->nullable();
            $table->decimal('order_value', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->string('currency', 3)->default('USD');
            $table->string('country_code', 3)->nullable();
            $table->string('customer_type')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->date('order_date');
            $table->date('purchase_date');
            $table->timestamp('last_updated')->nullable();
            $table->json('metadata')->nullable(); // Store additional purchase data
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['network_id', 'order_date']);
            $table->index(['campaign_id', 'order_date']);
            $table->index(['user_id', 'order_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
