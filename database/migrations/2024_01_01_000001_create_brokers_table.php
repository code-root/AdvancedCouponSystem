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
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('api_url')->nullable();
            $table->string('auth_url')->nullable();
            $table->string('callback_url')->nullable();
            $table->text('token')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('agency_id')->nullable();
            $table->json('credentials')->nullable(); // Store additional credentials as JSON
            $table->json('api_settings')->nullable(); // Store API-specific settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_connected')->default(false);
            $table->timestamp('last_sync')->nullable();
            $table->json('supported_features')->nullable(); // Store supported features like 'coupons', 'links', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
