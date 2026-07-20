<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_response_code', 20)->nullable()->after('payment_status');
            $table->string('payment_response_message', 500)->nullable()->after('payment_response_code');
            $table->timestamp('payment_failed_at')->nullable()->after('payment_response_message');
        });

        Schema::table('abandoned_carts', function (Blueprint $table) {
            $table->string('recovery_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('help_email_sent_at')->nullable()->after('abandoned_at');
            $table->string('feedback_reason', 40)->nullable()->after('help_email_sent_at');
            $table->text('feedback_message')->nullable()->after('feedback_reason');
            $table->timestamp('feedback_at')->nullable()->after('feedback_message');
        });

        Schema::create('business_events', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->index();
            $table->string('severity', 15)->default('warning')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 160);
            $table->string('message', 500)->nullable();
            $table->string('reference', 100)->nullable()->index();
            $table->timestamps();
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_events');
        Schema::table('abandoned_carts', function (Blueprint $table) {
            $table->dropColumn(['recovery_token', 'help_email_sent_at', 'feedback_reason', 'feedback_message', 'feedback_at']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_response_code', 'payment_response_message', 'payment_failed_at']);
        });
    }
};
