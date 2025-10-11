<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table to modify ENUM
        // For MySQL/PostgreSQL, you can use ALTER TABLE
        
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we'll just note this
            // The original migration has been updated to include 'previous_month'
            // New installations will have it automatically
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE sync_schedules MODIFY COLUMN date_range_type ENUM('today', 'yesterday', 'last_7_days', 'last_30_days', 'current_month', 'previous_month', 'custom') DEFAULT 'today'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE sync_schedules MODIFY COLUMN date_range_type ENUM('today', 'yesterday', 'last_7_days', 'last_30_days', 'current_month', 'custom') DEFAULT 'today'");
        }
    }
};
