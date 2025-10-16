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
        Schema::create('network_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('network_name'); // platformance, marketeers, etc.
            $table->string('session_key'); // MD5 hash of email+password
            $table->text('session_data'); // JSON encoded session data
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['network_name', 'session_key']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_sessions');
    }
};