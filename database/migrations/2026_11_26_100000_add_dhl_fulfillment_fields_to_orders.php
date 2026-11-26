<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_service_code', 40)->nullable()->after('shipping_provider');
            $table->string('shipping_service_name')->nullable()->after('shipping_service_code');
            $table->decimal('shipping_provider_cost', 12, 2)->nullable()->after('shipping_currency');
            $table->timestamp('shipping_estimated_delivery_at')->nullable()->after('shipping_packages');
            $table->string('shipping_quote_reference')->nullable()->after('shipping_estimated_delivery_at');

            $table->string('dhl_tracking_number', 40)->nullable()->unique()->after('shipping_quote_reference');
            $table->string('dhl_dispatch_confirmation_number', 80)->nullable()->after('dhl_tracking_number');
            $table->text('dhl_tracking_url')->nullable()->after('dhl_dispatch_confirmation_number');
            $table->text('dhl_odd_url')->nullable()->after('dhl_tracking_url');
            $table->string('dhl_shipment_status', 40)->nullable()->after('dhl_odd_url');
            $table->string('dhl_shipment_environment', 20)->nullable()->after('dhl_shipment_status');
            $table->timestamp('dhl_planned_shipping_at')->nullable()->after('dhl_shipment_environment');
            $table->timestamp('dhl_shipment_created_at')->nullable()->after('dhl_planned_shipping_at');
            $table->timestamp('dhl_last_tracking_at')->nullable()->after('dhl_shipment_created_at');
            $table->json('dhl_tracking_events')->nullable()->after('dhl_last_tracking_at');
            $table->json('dhl_documents')->nullable()->after('dhl_tracking_events');
            $table->json('dhl_warnings')->nullable()->after('dhl_documents');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('shipping_hs_code', 20)->nullable()->after('shipping_measurement_estimated');
            $table->string('shipping_country_of_origin', 2)->nullable()->after('shipping_hs_code');
            $table->string('shipping_customs_description')->nullable()->after('shipping_country_of_origin');
            $table->boolean('shipping_is_dangerous_goods')->default(false)->after('shipping_customs_description');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'shipping_hs_code',
                'shipping_country_of_origin',
                'shipping_customs_description',
                'shipping_is_dangerous_goods',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['dhl_tracking_number']);
            $table->dropColumn([
                'shipping_service_code',
                'shipping_service_name',
                'shipping_provider_cost',
                'shipping_estimated_delivery_at',
                'shipping_quote_reference',
                'dhl_tracking_number',
                'dhl_dispatch_confirmation_number',
                'dhl_tracking_url',
                'dhl_odd_url',
                'dhl_shipment_status',
                'dhl_shipment_environment',
                'dhl_planned_shipping_at',
                'dhl_shipment_created_at',
                'dhl_last_tracking_at',
                'dhl_tracking_events',
                'dhl_documents',
                'dhl_warnings',
            ]);
        });
    }
};
