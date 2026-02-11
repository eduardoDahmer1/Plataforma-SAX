<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a tabela já existir por algum erro prévio, nós a removemos para criar do zero com a estrutura nova
        Schema::dropIfExists('palaces');

        Schema::create('palaces', function (Blueprint $table) {
            $table->id();
            
            // SEÇÃO 01: HERO
            $table->string('hero_titulo')->nullable();
            $table->text('hero_descricao')->nullable();
            $table->string('hero_imagem')->nullable();

            // SEÇÃO 02: SOMMELIER / BAR
            $table->string('bar_titulo')->default('Bar e Bodega');
            $table->text('bar_descricao')->nullable();
            $table->string('bar_imagem_1')->nullable();
            $table->string('bar_imagem_2')->nullable();
            $table->string('bar_imagem_3')->nullable();

            // SEÇÃO 03: EVENTOS
            $table->string('eventos_titulo')->default('Celebre Conosco');
            $table->text('eventos_descricao')->nullable();
            $table->json('eventos_galeria')->nullable();

            // SEÇÃO 04: NOITE ÁRABE (TEMÁTICA)
            $table->string('tematica_tag')->nullable();
            $table->string('tematica_titulo')->nullable();
            $table->text('tematica_descricao')->nullable();
            $table->string('tematica_preco')->nullable();
            $table->string('tematica_imagem')->nullable();

            // SEÇÃO 05: GASTRONOMIA
            $table->string('gastronomia_titulo')->default('A Arte de Servir');
            $table->text('gastronomia_cafe_desc')->nullable();
            $table->text('gastronomia_almoco_desc')->nullable();
            $table->text('gastronomia_jantar_desc')->nullable();

            // SEÇÃO 06: LOCALIZAÇÃO / CONTATO
            $table->string('contato_endereco')->nullable();
            $table->string('contato_horario_segunda')->nullable();
            $table->string('contato_horario_sabado')->nullable();
            $table->string('contato_horario_domingo')->nullable();
            $table->string('contato_whatsapp')->nullable();
            $table->text('contato_mapa_iframe')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palaces');
    }
};