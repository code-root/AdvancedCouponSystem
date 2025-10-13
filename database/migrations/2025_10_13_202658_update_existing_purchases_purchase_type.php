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
        // Update existing purchases to set purchase_type based on coupon_id
        DB::statement("UPDATE purchases SET purchase_type = 'coupon' WHERE coupon_id IS NOT NULL");
        DB::statement("UPDATE purchases SET purchase_type = 'link' WHERE coupon_id = 247 ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset purchase_type to default
        DB::statement("UPDATE purchases SET purchase_type = 'coupon'");
    }
};
