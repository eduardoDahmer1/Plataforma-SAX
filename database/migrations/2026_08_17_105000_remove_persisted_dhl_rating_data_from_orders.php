<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'shipping_service_code',
                'shipping_service_name',
                'shipping_provider_cost',
                'shipping_estimated_delivery_at',
                'shipping_quote_reference',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_service_code', 40)->nullable()->after('shipping_provider');
            $table->string('shipping_service_name')->nullable()->after('shipping_service_code');
            $table->decimal('shipping_provider_cost', 12, 2)->nullable()->after('shipping_currency');
            $table->timestamp('shipping_estimated_delivery_at')->nullable()->after('shipping_packages');
            $table->string('shipping_quote_reference')->nullable()->after('shipping_estimated_delivery_at');
        });
    }
};
