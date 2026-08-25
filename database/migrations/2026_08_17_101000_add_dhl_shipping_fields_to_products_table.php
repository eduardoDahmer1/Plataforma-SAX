<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('shipping_weight_kg', 10, 3)->nullable()->after('stock');
            $table->decimal('shipping_length_cm', 10, 2)->nullable()->after('shipping_weight_kg');
            $table->decimal('shipping_width_cm', 10, 2)->nullable()->after('shipping_length_cm');
            $table->decimal('shipping_height_cm', 10, 2)->nullable()->after('shipping_width_cm');
            $table->string('shipping_hs_code', 20)->nullable()->after('shipping_height_cm');
            $table->string('shipping_country_of_origin', 2)->nullable()->after('shipping_hs_code');
            $table->string('shipping_customs_description')->nullable()->after('shipping_country_of_origin');
            $table->boolean('shipping_is_dangerous_goods')->default(false)->after('shipping_customs_description');

            $table->index('shipping_hs_code');
            $table->index('shipping_country_of_origin');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['shipping_hs_code']);
            $table->dropIndex(['shipping_country_of_origin']);
            $table->dropColumn([
                'shipping_weight_kg',
                'shipping_length_cm',
                'shipping_width_cm',
                'shipping_height_cm',
                'shipping_hs_code',
                'shipping_country_of_origin',
                'shipping_customs_description',
                'shipping_is_dangerous_goods',
            ]);
        });
    }
};
