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
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->boolean('is_online')->default(true)->after('is_active');
            $table->timestamp('last_heartbeat')->nullable()->after('last_activity');
            // Index for quick online status checks
            $table->index(['is_online', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropIndex(['is_online', 'is_active']);
            $table->dropColumn(['is_online', 'last_heartbeat']);
        });
    }
};
