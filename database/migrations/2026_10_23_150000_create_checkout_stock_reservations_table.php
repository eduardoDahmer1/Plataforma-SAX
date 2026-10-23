<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('held');
            $table->timestamp('expires_at')->index();
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason', 60)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'order_id'], 'checkout_stock_user_status_order_idx');
            $table->index(['product_id', 'status', 'expires_at'], 'checkout_stock_product_status_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_stock_reservations');
    }
};
