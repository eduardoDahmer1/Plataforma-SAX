<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->boolean('cart_enabled')->default(true)->after('maintenance');
            $table->boolean('checkout_enabled')->default(true)->after('cart_enabled');
            $table->boolean('add_to_cart_enabled')->default(true)->after('checkout_enabled');
            $table->boolean('deposit_enabled')->default(true)->after('add_to_cart_enabled');
            $table->boolean('bancard_enabled')->default(true)->after('deposit_enabled');
            $table->boolean('pix_enabled')->default(true)->after('bancard_enabled');
            $table->boolean('whatsapp_enabled')->default(true)->after('pix_enabled');
            $table->boolean('geonames_enabled')->default(false)->after('whatsapp_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'cart_enabled', 'checkout_enabled', 'add_to_cart_enabled',
                'deposit_enabled', 'bancard_enabled', 'pix_enabled',
                'whatsapp_enabled', 'geonames_enabled',
            ]);
        });
    }
};
