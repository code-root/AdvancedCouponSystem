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
        Schema::table('network_connections', function (Blueprint $table) {
            $table->string('client_id')->nullable()->after('connection_name');
            $table->string('client_secret')->nullable()->after('client_id');
            $table->text('token')->nullable()->after('client_secret');
            $table->string('contact_id')->nullable()->after('token');
            $table->string('api_endpoint')->nullable()->after('contact_id');
            $table->string('status')->default('pending')->after('api_endpoint'); // pending, connected, disconnected, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_connections', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'client_secret', 'token', 'contact_id', 'api_endpoint', 'status']);
        });
    }
};
