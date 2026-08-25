<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('product_brand')->nullable()->after('sku');
            $table->string('product_size', 80)->nullable()->after('product_brand');
            $table->string('product_color', 80)->nullable()->after('product_size');
            $table->string('shipping_profile', 40)->nullable()->after('product_color');
            $table->decimal('shipping_weight_kg', 10, 3)->nullable()->after('shipping_profile');
            $table->decimal('shipping_length_cm', 10, 2)->nullable()->after('shipping_weight_kg');
            $table->decimal('shipping_width_cm', 10, 2)->nullable()->after('shipping_length_cm');
            $table->decimal('shipping_height_cm', 10, 2)->nullable()->after('shipping_width_cm');
            $table->boolean('shipping_measurement_estimated')->default(true)->after('shipping_height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'product_brand', 'product_size', 'product_color', 'shipping_profile',
                'shipping_weight_kg', 'shipping_length_cm', 'shipping_width_cm',
                'shipping_height_cm', 'shipping_measurement_estimated',
            ]);
        });
    }
};
