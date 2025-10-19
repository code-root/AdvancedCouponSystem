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
        Schema::create('admin_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('device_name')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->timestamp('login_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('logout_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('location')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'is_active']);
            $table->index(['login_at']);
            $table->index(['last_activity_at']);
            $table->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_sessions');
    }
};