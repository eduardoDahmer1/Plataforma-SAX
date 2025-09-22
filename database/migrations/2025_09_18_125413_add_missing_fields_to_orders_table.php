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
        Schema::table('orders', function (Blueprint $table) {
            // Identificadores de pagamento
            $table->string('order_number')->nullable()->after('id');
            $table->string('txnid')->nullable()->after('shop_process_id');
            $table->string('charge_id')->nullable()->after('txnid');
            $table->string('pay_id')->nullable()->after('charge_id');

            // Status de pagamento separado do status do pedido
            $table->string('payment_status')->default('pending')->after('status');

            // Custos adicionais
            $table->decimal('shipping_cost', 10, 2)->nullable()->after('shipping');
            $table->decimal('packing_cost', 10, 2)->nullable()->after('shipping_cost');
            $table->decimal('tax', 10, 2)->nullable()->after('packing_cost');

            // Moeda
            $table->string('currency_sign', 10)->nullable()->after('tax');
            $table->decimal('currency_value', 10, 4)->nullable()->after('currency_sign');

            // Endereço de entrega separado
            $table->string('shipping_name')->nullable()->after('name');
            $table->string('shipping_email')->nullable()->after('email');
            $table->string('shipping_phone')->nullable()->after('phone');
            $table->string('shipping_country')->nullable()->after('country');
            $table->string('shipping_state')->nullable()->after('state');
            $table->string('shipping_city')->nullable()->after('city');
            $table->string('shipping_zip')->nullable()->after('cep');
            $table->string('shipping_address')->nullable()->after('address');
            $table->string('shipping_address_number')->nullable()->after('number');
            $table->string('shipping_complement')->nullable()->after('shipping_address_number');
            $table->string('shipping_district')->nullable()->after('shipping_complement');
            $table->string('shipping_document')->nullable()->after('shipping_district');

            // Campos extras importantes
            $table->text('order_note')->nullable()->after('observations');
            $table->text('internal_note')->nullable()->after('order_note');
            $table->string('affilate_user')->nullable()->after('internal_note');
            $table->decimal('affilate_charge', 10, 2)->nullable()->after('affilate_user');

            // Delivery info
            $table->string('location')->nullable()->after('store');
            $table->string('delivery_method')->nullable()->after('location');
            $table->text('description')->nullable()->after('delivery_method');
            $table->decimal('payment')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'txnid',
                'charge_id',
                'pay_id',
                'payment_status',
                'shipping_cost',
                'packing_cost',
                'tax',
                'currency_sign',
                'currency_value',
                'shipping_name',
                'shipping_email',
                'shipping_phone',
                'shipping_country',
                'shipping_state',
                'shipping_city',
                'shipping_zip',
                'shipping_address',
                'shipping_address_number',
                'shipping_complement',
                'shipping_district',
                'shipping_document',
                'order_note',
                'internal_note',
                'affilate_user',
                'affilate_charge',
                'location',
                'delivery_method',
                'description',
                'payment',
            ]);
        });
    }
};
