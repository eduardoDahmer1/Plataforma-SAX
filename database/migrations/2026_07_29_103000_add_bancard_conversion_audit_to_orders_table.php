<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_currency', 10)->nullable()->after('payment_method');
            $table->decimal('payment_amount', 18, 2)->nullable()->after('payment_currency');
            $table->decimal('payment_exchange_rate', 18, 6)->nullable()->after('payment_amount');
            $table->string('payment_card_country', 80)->nullable()->after('payment_exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_currency',
                'payment_amount',
                'payment_exchange_rate',
                'payment_card_country',
            ]);
        });
    }
};
