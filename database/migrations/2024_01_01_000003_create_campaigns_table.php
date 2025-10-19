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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained('networks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('network_campaign_id')->nullable(); // Campaign ID from network
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('advertiser_name')->nullable();
            $table->string('advertiser_id')->nullable();
            $table->enum('campaign_type', ['coupon', 'link', 'app'])->default('coupon');
            $table->enum('status', ['active', 'paused', 'inactive'])->default('active');
            $table->json('settings')->nullable(); // Store campaign-specific settings
            $table->timestamps();
            
            // Allow duplicate campaigns per network
            // $table->unique(['network_id', 'network_campaign_id']); // Removed to allow duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
