<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('environment', 20)->default('sandbox');
            $table->string('external_id', 100)->nullable();
            $table->string('control_number', 100);
            $table->string('status', 30)->default('pending');
            $table->string('provider_status', 30)->nullable();
            $table->string('foreign_currency', 10)->nullable();
            $table->decimal('foreign_amount', 18, 2)->nullable();
            $table->string('national_currency', 10)->nullable();
            $table->decimal('national_amount', 18, 2)->nullable();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->longText('qr_code_base64')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('failure_code', 50)->nullable();
            $table->text('failure_message')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'control_number']);
            $table->unique(['provider', 'external_id']);
            $table->index(['order_id', 'provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
