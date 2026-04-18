<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            // Campos para o endereço do comprador (se não existirem)
            if (!Schema::hasColumn('orders', 'number')) {
                $blueprint->string('number', 20)->nullable()->after('address');
            }
            if (!Schema::hasColumn('orders', 'district')) {
                $blueprint->string('district', 100)->nullable()->after('number');
            }
            if (!Schema::hasColumn('orders', 'complement')) {
                $blueprint->string('complement', 150)->nullable()->after('district');
            }

            // Campos para o endereço de ENVIO (Shipping)
            // Note que usei os nomes que você já tem na estrutura (shipping_address_number, etc)
            if (!Schema::hasColumn('orders', 'shipping_address_number')) {
                $blueprint->string('shipping_address_number', 20)->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'shipping_district')) {
                $blueprint->string('shipping_district', 100)->nullable()->after('shipping_address_number');
            }
            if (!Schema::hasColumn('orders', 'shipping_complement')) {
                $blueprint->string('shipping_complement', 150)->nullable()->after('shipping_district');
            }
            
            // Garantindo que o campo de observações e país existam
            if (!Schema::hasColumn('orders', 'observations')) {
                $blueprint->text('observations')->nullable()->after('shipping_complement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'number', 
                'district', 
                'complement', 
                'shipping_address_number', 
                'shipping_district', 
                'shipping_complement',
                'observations'
            ]);
        });
    }
};