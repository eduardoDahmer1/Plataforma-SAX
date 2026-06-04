<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            
            // Chaves Polimórficas + Idioma
            $table->string('page_type'); // Ex: 'institucional', 'palace', 'bridal', 'cafe_bistro'
            $table->unsignedBigInteger('page_id'); // ID do registro na tabela pai
            $table->string('locale', 5); // 'pt-br', 'es', 'en'
            
            // --- CAMPOS DA PÁGINA: PALACES ---
            $table->string('palace_hero_titulo')->nullable();
            $table->text('palace_hero_descricao')->nullable();
            $table->string('palace_bar_titulo')->nullable();
            $table->text('palace_bar_descricao')->nullable();
            $table->string('palace_eventos_titulo')->nullable();
            $table->text('palace_eventos_descricao')->nullable();
            $table->string('palace_tematica_tag')->nullable();
            $table->string('palace_tematica_titulo')->nullable();
            $table->text('palace_tematica_descricao')->nullable();
            $table->string('palace_tematica_preco')->nullable();
            $table->string('palace_gastronomia_titulo')->nullable();
            $table->text('palace_gastronomia_cafe_desc')->nullable();
            $table->text('palace_gastronomia_almoco_desc')->nullable();
            $table->text('palace_gastronomia_jantar_desc')->nullable();
            $table->string('palace_contato_endereco')->nullable();
            $table->string('palace_contato_horario_segunda')->nullable();
            $table->string('palace_contato_horario_sabado')->nullable();
            $table->string('palace_contato_horario_domingo')->nullable();

            // --- CAMPOS DA PÁGINA: BRIDALS ---
            $table->string('bridal_title')->nullable();
            $table->string('bridal_meta_title')->nullable();
            $table->text('bridal_meta_description')->nullable();
            $table->string('bridal_hero_title')->nullable();
            $table->string('bridal_hero_subtitle')->nullable();
            $table->text('bridal_hero_description')->nullable();
            $table->string('bridal_services_label')->nullable();
            $table->string('bridal_services_title')->nullable();
            $table->string('bridal_services_cta_text')->nullable();
            $table->text('bridal_services')->nullable(); // JSON ou texto longo para a lista de serviços
            $table->string('bridal_palace_subtitle')->nullable();
            $table->string('bridal_palace_title')->nullable();
            $table->text('bridal_palace_description')->nullable();
            $table->string('bridal_testimonials_label')->nullable();
            $table->string('bridal_testimonials_title')->nullable();
            $table->text('bridal_testimonials')->nullable(); // JSON ou texto longo para depoimentos
            $table->text('bridal_locations')->nullable(); // JSON ou texto longo para localizações

            // --- CAMPOS DA PÁGINA: INSTITUCIONAL ---
            $table->string('inst_section_one_title')->nullable();
            $table->text('inst_section_one_content')->nullable();
            $table->string('inst_text_section_one_title')->nullable();
            $table->text('inst_text_section_one_body')->nullable();
            $table->string('inst_text_section_two_title')->nullable();
            $table->text('inst_text_section_two_body')->nullable();
            $table->string('inst_text_section_three_title')->nullable();
            $table->text('inst_text_section_three_body')->nullable();

            // --- CAMPOS DA PÁGINA: CAFE_BISTROS ---
            $table->string('cafe_meta_title')->nullable();
            $table->text('cafe_meta_description')->nullable();
            $table->string('cafe_hero_titulo')->nullable();
            $table->string('cafe_hero_subtitulo')->nullable();
            $table->string('cafe_sobre_titulo')->nullable();
            $table->text('cafe_sobre_texto')->nullable();
            $table->string('cafe_cardapio_titulo')->nullable();
            $table->string('cafe_cardapio_subtitulo')->nullable();
            $table->string('cafe_eventos_titulo')->nullable();
            $table->string('cafe_eventos_subtitulo')->nullable();
            $table->text('cafe_eventos_texto')->nullable();
            $table->text('cafe_eventos_tipos')->nullable(); // JSON ou texto para tipos de eventos
            $table->text('cafe_horarios')->nullable(); // JSON ou texto explicativo de horários
            $table->string('cafe_direccion')->nullable();

            $table->timestamps();

            // Índices de performance e unicidade para não duplicar traduções
            $table->index(['page_type', 'page_id']);
            $table->unique(['page_type', 'page_id', 'locale'], 'page_translations_poly_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_translations');
    }
};