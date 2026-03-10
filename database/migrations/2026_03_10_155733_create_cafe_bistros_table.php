<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('cafe_bistros', function (Blueprint $table) {
            $table->id();

            
            // General
            $table->boolean('is_active')->default(true);
            $table->string('whatsapp')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Hero
            $table->string('hero_imagen')->nullable();
            $table->string('hero_titulo')->nullable();
            $table->text('hero_subtitulo')->nullable();

            // Sobre Nós
            $table->string('sobre_imagen')->nullable();
            $table->string('sobre_titulo')->nullable();
            $table->text('sobre_texto')->nullable();

            // Cardápio
            $table->string('cardapio_titulo')->nullable();
            $table->text('cardapio_subtitulo')->nullable();
            $table->string('cardapio_pdf')->nullable();

            // Eventos
            $table->string('eventos_titulo')->nullable();
            $table->text('eventos_subtitulo')->nullable();
            $table->text('eventos_texto')->nullable();
            $table->json('eventos_tipos')->nullable();
            $table->json('eventos_galeria')->nullable();

            // Horários
            $table->json('horarios')->nullable();

            // Contacto
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->text('mapa_embed')->nullable();

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('cafe_bistros');
    }
};
