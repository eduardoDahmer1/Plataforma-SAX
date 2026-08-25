<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('shipping_profile', 40)->nullable()->after('shipping_is_dangerous_goods')->index();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_provider', 40)->nullable()->after('shipping_cost');
            $table->string('shipping_service_code', 40)->nullable()->after('shipping_provider');
            $table->string('shipping_service_name')->nullable()->after('shipping_service_code');
            $table->string('shipping_currency', 3)->nullable()->after('shipping_service_name');
            $table->decimal('shipping_provider_cost', 12, 2)->nullable()->after('shipping_currency');
            $table->decimal('shipping_markup_percent', 6, 2)->nullable()->after('shipping_provider_cost');
            $table->decimal('shipping_billable_weight_kg', 10, 3)->nullable()->after('shipping_markup_percent');
            $table->unsignedInteger('shipping_package_count')->nullable()->after('shipping_billable_weight_kg');
            $table->json('shipping_packages')->nullable()->after('shipping_package_count');
            $table->timestamp('shipping_estimated_delivery_at')->nullable()->after('shipping_packages');
            $table->string('shipping_quote_reference')->nullable()->after('shipping_estimated_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'shipping_provider', 'shipping_service_code', 'shipping_service_name',
                'shipping_currency', 'shipping_provider_cost', 'shipping_markup_percent',
                'shipping_billable_weight_kg', 'shipping_package_count', 'shipping_packages',
                'shipping_estimated_delivery_at', 'shipping_quote_reference',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['shipping_profile']);
            $table->dropColumn('shipping_profile');
        });
    }
};
