<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('palaces', function (Blueprint $table) {
            $table->id();
            $table->string('titulo'); // Ex: Noite Árabe, Bar e Bodega
            $table->text('descricao'); // Descrição geral do serviço ou evento
            $table->longText('detalhes'); // Informações detalhadas, preços e horários
            
            // Campos de Mídia
            $table->string('slider_principal'); // Imagem de destaque no topo
            $table->json('galeria')->nullable(); // Armazena múltiplos caminhos de imagem
            
            // 3 Campos adicionais para imagens específicas
            $table->string('imagem_gastronomia')->nullable(); // Fotos de pratos ou buffet
            $table->string('imagem_bebidas')->nullable(); // Fotos da bodega/vinhos
            $table->string('imagem_eventos')->nullable(); // Fotos de casamentos/decor
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palaces');
    }
};