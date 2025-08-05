<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCredentialsToPaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->json('credentials')->nullable()->after('description');

            // Se tiver campos public_key e private_key separados, remova:
            if (Schema::hasColumn('payment_methods', 'public_key')) {
                $table->dropColumn('public_key');
            }
            if (Schema::hasColumn('payment_methods', 'private_key')) {
                $table->dropColumn('private_key');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('credentials');

            // Se quiser voltar, cria os campos separados (opcional):
            $table->string('public_key')->nullable()->after('description');
            $table->string('private_key')->nullable()->after('public_key');
        });
    }
}
