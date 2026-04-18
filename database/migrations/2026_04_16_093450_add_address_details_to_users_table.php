<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Se você já tem 'location_country', vamos renomear para 'country' 
            // ou garantir que o campo 'country' exista para bater com o form
            if (Schema::hasColumn('users', 'location_country')) {
                $table->renameColumn('location_country', 'country');
            } elseif (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('phone_number');
            }

            // Adicionando Número, Bairro e Complemento
            if (!Schema::hasColumn('users', 'number')) {
                $table->string('number')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('number');
            }
            if (!Schema::hasColumn('users', 'complement')) {
                $table->string('complement')->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['number', 'district', 'complement']);
            // Opcional: voltar o nome do país se necessário
            // $table->renameColumn('country', 'location_country');
        });
    }
};