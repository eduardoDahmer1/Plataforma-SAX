<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Dados pessoais
            $table->string('name')->nullable();
            $table->string('document')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Endereço
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('cep')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->text('observations')->nullable();

            // Tipo de envio
            $table->tinyInteger('shipping')->nullable(); // 1 = endereço cadastrado, 2 = alternativo, 3 = loja
            $table->tinyInteger('store')->nullable();    // se retirado na loja
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'document', 'email', 'phone',
                'address', 'city', 'state', 'country', 'cep', 'street', 'number', 'observations',
                'shipping', 'store'
            ]);
        });
    }
};
