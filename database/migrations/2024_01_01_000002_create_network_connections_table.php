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
        Schema::create('network_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('network_id')->constrained('networks')->onDelete('cascade');
            $table->string('connection_name')->nullable(); // User-defined name for the connection
            $table->string('api_endpoint')->nullable(); // Network API endpoint
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->json('credentials')->nullable(); // Store user-specific credentials (encrypted)
            $table->json('api_settings')->nullable(); // Store API-specific settings
            $table->json('sync_settings')->nullable(); // Store sync settings
            $table->string('status')->default('pending'); // pending, active, inactive
            $table->boolean('is_connected')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('error_log')->nullable(); // Store error logs
            $table->timestamps();
            
            // Ensure one connection per user per network
            $table->unique(['user_id', 'network_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_connections');
    }
};
