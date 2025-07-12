<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(); // Adiciona a coluna de chave estrangeira
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade'); // Relacionamento com a tabela products
        });
    }
    
    public function down()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
    
};
