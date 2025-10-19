<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('gateway')->nullable(); // stripe, paypal
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('external_id')->nullable();
            $table->string('receipt_url')->nullable();
            $table->json('meta')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};




