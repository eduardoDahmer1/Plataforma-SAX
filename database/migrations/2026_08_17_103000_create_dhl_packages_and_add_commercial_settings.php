<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->decimal('volumetric_divisor', 10, 2)->default(5000)->after('excluded_country_codes');
            $table->decimal('rate_markup_percent', 6, 2)->default(5)->after('volumetric_divisor');
            $table->boolean('fallback_measurements_enabled')->default(true)->after('rate_markup_percent');
            $table->boolean('free_shipping_enabled')->default(false)->after('fallback_measurements_enabled');
            $table->unsignedInteger('free_shipping_min_items')->nullable()->after('free_shipping_enabled');
            $table->decimal('free_shipping_min_subtotal', 12, 2)->nullable()->after('free_shipping_min_items');
            $table->decimal('free_shipping_max_billable_weight_kg', 10, 3)->nullable()->after('free_shipping_min_subtotal');
        });

        Schema::create('dhl_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 120);
            $table->string('dhl_package_code', 20)->nullable();
            $table->boolean('active')->default(true);
            $table->decimal('length_cm', 10, 2);
            $table->decimal('width_cm', 10, 2);
            $table->decimal('height_cm', 10, 2);
            $table->decimal('tare_weight_kg', 10, 3)->default(0);
            $table->decimal('max_gross_weight_kg', 10, 3);
            $table->unsignedInteger('max_items')->default(1);
            $table->decimal('fill_ratio_percent', 5, 2)->default(70);
            $table->text('product_examples')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('dhl_packages')->insert([
            [
                'code' => 'small', 'name' => 'Caixa pequena — DHL Box 2', 'dhl_package_code' => '2BX',
                'active' => true, 'length_cm' => 33, 'width_cm' => 18, 'height_cm' => 10,
                'tare_weight_kg' => 0.150, 'max_gross_weight_kg' => 1.2, 'max_items' => 3,
                'fill_ratio_percent' => 70, 'product_examples' => 'Óculos, carteira, cinto, boné, joias e pequenos itens; faixa operacional de 300 g a 1 kg de mercadoria.',
                'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'code' => 'medium', 'name' => 'Caixa média — DHL Box 4', 'dhl_package_code' => '4BX',
                'active' => true, 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 18,
                'tare_weight_kg' => 0.320, 'max_gross_weight_kg' => 5, 'max_items' => 6,
                'fill_ratio_percent' => 70, 'product_examples' => 'Até 5 roupas leves, 2 calças, 1 par de calçados ou 1 bolsa pequena.',
                'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'code' => 'large', 'name' => 'Caixa grande — DHL Box 5', 'dhl_package_code' => '5BX',
                'active' => true, 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 34,
                'tare_weight_kg' => 0.770, 'max_gross_weight_kg' => 10, 'max_items' => 10,
                'fill_ratio_percent' => 70, 'product_examples' => 'Até 8 roupas leves, 2 pares de calçados, casacos, bolsas e combinações maiores.',
                'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dhl_packages');

        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'volumetric_divisor', 'rate_markup_percent', 'fallback_measurements_enabled',
                'free_shipping_enabled', 'free_shipping_min_items', 'free_shipping_min_subtotal',
                'free_shipping_max_billable_weight_kg',
            ]);
        });
    }
};
