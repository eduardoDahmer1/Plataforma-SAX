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
            $table->unsignedBigInteger('test_package_id')->nullable()->after('test_height_cm');
        });

        $now = now();
        $packages = [
            ['code' => 'envelope_1', 'name' => 'Envelope 1 — DHL Card Envelope', 'dhl_package_code' => '1CE', 'length_cm' => 35, 'width_cm' => 27.5, 'height_cm' => 2, 'tare_weight_kg' => 0, 'max_gross_weight_kg' => .5, 'max_items' => 1, 'fill_ratio_percent' => 90, 'product_examples' => 'Documentos e itens muito finos que não amassam.', 'sort_order' => 10],
            ['code' => 'small', 'name' => 'DHL Box 2 — até 1 kg', 'dhl_package_code' => '2BX', 'length_cm' => 33.7, 'width_cm' => 18.2, 'height_cm' => 8.1, 'tare_weight_kg' => .15, 'max_gross_weight_kg' => 1, 'max_items' => 3, 'fill_ratio_percent' => 70, 'product_examples' => 'Óculos, carteira, cinto, boné, joias e pequenos acessórios.', 'sort_order' => 20],
            ['code' => 'box_3', 'name' => 'DHL Box 3 — até 2 kg', 'dhl_package_code' => '3BX', 'length_cm' => 33.7, 'width_cm' => 32.2, 'height_cm' => 9.2, 'tare_weight_kg' => .24, 'max_gross_weight_kg' => 2, 'max_items' => 4, 'fill_ratio_percent' => 70, 'product_examples' => 'Roupas dobradas, acessórios e calçados baixos.', 'sort_order' => 30],
            ['code' => 'medium', 'name' => 'DHL Box 4 — até 5 kg', 'dhl_package_code' => '4BX', 'length_cm' => 33.7, 'width_cm' => 32.2, 'height_cm' => 18, 'tare_weight_kg' => .32, 'max_gross_weight_kg' => 5, 'max_items' => 6, 'fill_ratio_percent' => 70, 'product_examples' => 'Até 5 roupas leves, 2 calças, 1 par de calçados ou 1 bolsa pequena.', 'sort_order' => 40],
            ['code' => 'large', 'name' => 'DHL Box 5 — até 10 kg', 'dhl_package_code' => '5BX', 'length_cm' => 33.7, 'width_cm' => 32.2, 'height_cm' => 34.5, 'tare_weight_kg' => .77, 'max_gross_weight_kg' => 10, 'max_items' => 10, 'fill_ratio_percent' => 70, 'product_examples' => 'Roupas, dois pares de calçados, casacos, bolsas e combinações maiores.', 'sort_order' => 50],
            ['code' => 'box_6', 'name' => 'DHL Box 6 — até 15 kg', 'dhl_package_code' => '6BX', 'length_cm' => 41.7, 'width_cm' => 35.9, 'height_cm' => 36.9, 'tare_weight_kg' => .98, 'max_gross_weight_kg' => 15, 'max_items' => 14, 'fill_ratio_percent' => 70, 'product_examples' => 'Pedidos maiores de roupas, calçados, bolsas e artigos para casa.', 'sort_order' => 60],
            ['code' => 'box_7', 'name' => 'DHL Box 7 — até 20 kg', 'dhl_package_code' => '7BX', 'length_cm' => 48.1, 'width_cm' => 40.4, 'height_cm' => 38.9, 'tare_weight_kg' => 1.18, 'max_gross_weight_kg' => 20, 'max_items' => 18, 'fill_ratio_percent' => 70, 'product_examples' => 'Pedidos volumosos com várias roupas, calçados, bolsas e itens para casa.', 'sort_order' => 70],
            ['code' => 'box_8', 'name' => 'DHL Box 8 — até 25 kg', 'dhl_package_code' => '8BX', 'length_cm' => 54.1, 'width_cm' => 44.4, 'height_cm' => 40.9, 'tare_weight_kg' => 1.48, 'max_gross_weight_kg' => 25, 'max_items' => 22, 'fill_ratio_percent' => 70, 'product_examples' => 'Maior caixa padronizada para pedidos grandes e volumosos.', 'sort_order' => 80],
        ];

        foreach ($packages as $package) {
            $query = DB::table('dhl_packages')->where('code', $package['code']);
            $values = $package + ['active' => true, 'updated_at' => $now];

            if ($query->exists()) {
                $query->update($values);
            } else {
                DB::table('dhl_packages')->insert($values + ['created_at' => $now]);
            }
        }

        $defaultPackageId = DB::table('dhl_packages')->where('code', 'small')->value('id');
        DB::table('dhl_settings')->whereNull('test_package_id')->update(['test_package_id' => $defaultPackageId]);
    }

    public function down(): void
    {
        DB::table('dhl_packages')->whereIn('code', ['envelope_1', 'box_3', 'box_6', 'box_7', 'box_8'])->delete();

        $now = now();
        $originalPackages = [
            ['code' => 'small', 'name' => 'Caixa pequena — DHL Box 2', 'length_cm' => 33, 'width_cm' => 18, 'height_cm' => 10, 'max_gross_weight_kg' => 1.2, 'product_examples' => 'Óculos, carteira, cinto, boné, joias e pequenos itens; faixa operacional de 300 g a 1 kg de mercadoria.', 'sort_order' => 10],
            ['code' => 'medium', 'name' => 'Caixa média — DHL Box 4', 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 18, 'max_gross_weight_kg' => 5, 'product_examples' => 'Até 5 roupas leves, 2 calças, 1 par de calçados ou 1 bolsa pequena.', 'sort_order' => 20],
            ['code' => 'large', 'name' => 'Caixa grande — DHL Box 5', 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 34, 'max_gross_weight_kg' => 10, 'product_examples' => 'Até 8 roupas leves, 2 pares de calçados, casacos, bolsas e combinações maiores.', 'sort_order' => 30],
        ];
        foreach ($originalPackages as $package) {
            DB::table('dhl_packages')->where('code', $package['code'])->update($package + ['updated_at' => $now]);
        }

        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->dropColumn('test_package_id');
        });
    }
};
