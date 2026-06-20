<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Criamos um campo 'updated_by' para salvar o ID do usuário
            // Usamos nullable() para permitir registros que não foram editados por humanos
            $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            
            // Opcional: criar chave estrangeira ligando à tabela de users
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};