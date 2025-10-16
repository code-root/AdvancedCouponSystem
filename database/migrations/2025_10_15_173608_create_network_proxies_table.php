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
        Schema::create('network_proxies', function (Blueprint $table) {
            $table->id();
            $table->string('host'); // IP or hostname
            $table->unsignedInteger('port');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('scheme')->default('http'); // http or https
            $table->string('network')->index(); // e.g., 'marketeers'
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('fail_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['network', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_proxies');
    }
};
