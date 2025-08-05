<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankDetailsToPaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Adiciona a coluna 'bank_details' para salvar detalhes de conta bancária
            $table->text('bank_details')->nullable();
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Remove a coluna 'bank_details' caso a migration seja revertida
            $table->dropColumn('bank_details');
        });
    }
}
