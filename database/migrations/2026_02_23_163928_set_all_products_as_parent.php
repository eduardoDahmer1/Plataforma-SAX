<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Primeiro, removemos a restrição de 'not null' temporariamente para evitar o erro de truncamento
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_role')->nullable()->change();
            $table->text('parent_id')->nullable()->change();
        });

        // 2. Agora, atualizamos os dados existentes via Query Builder
        DB::table('products')->update([
            'product_role' => 'P',
            'parent_id' => null // ou o valor que você desejar
        ]);

        // 3. Agora que todos os dados são 'P', podemos aplicar o NOT NULL e o DEFAULT com segurança
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_role')->default('P')->nullable(false)->change();
        });
    }
};
