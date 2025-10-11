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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Device Information
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('device_name')->nullable(); // iPhone, Samsung Galaxy, etc.
            $table->string('platform')->nullable(); // iOS, Android, Windows, Mac, Linux
            $table->string('browser')->nullable(); // Chrome, Firefox, Safari, etc.
            $table->string('browser_version')->nullable();
            
            // Location Information
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Referrer and Entry Information
            $table->text('referrer_url')->nullable(); // من أين جاء قبل تسجيل الدخول
            $table->string('landing_page')->nullable(); // أول صفحة دخلها
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            
            // Session Details
            $table->text('payload')->nullable(); // Session data
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('logout_reason')->nullable(); // manual, expired, forced
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('session_id');
            $table->index('ip_address');
            $table->index('is_active');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
