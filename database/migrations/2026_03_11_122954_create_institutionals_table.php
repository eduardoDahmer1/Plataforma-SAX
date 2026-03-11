<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('institucional', function (Blueprint $blueprint) {
            $blueprint->id();
            
            // 1. Slider Superior (Armazenar caminhos em JSON ou String separada)
            $blueprint->json('top_sliders')->nullable();

            // 2. Seção Conteúdo + Imagem
            $blueprint->string('section_one_title')->nullable();
            $blueprint->text('section_one_content')->nullable();
            $blueprint->string('section_one_image')->nullable();

            // 3. Carrossel de Marcas (JSON com caminhos das logos)
            $blueprint->json('brand_logos')->nullable();

            // 4. Galeria com Modal (JSON com caminhos das fotos)
            $blueprint->json('gallery_images')->nullable();

            // 5. Três Seções de Texto
            $blueprint->string('text_section_one_title')->nullable();
            $blueprint->text('text_section_one_body')->nullable();
            
            $blueprint->string('text_section_two_title')->nullable();
            $blueprint->text('text_section_two_body')->nullable();
            
            $blueprint->string('text_section_three_title')->nullable();
            $blueprint->text('text_section_three_body')->nullable();

            // 6. Seção de Métricas (Números Fixos ou Editáveis)
            $blueprint->integer('stat_brands_count')->default(200);
            $blueprint->integer('stat_sqm_count')->default(22);
            $blueprint->integer('stat_employees_count')->default(400);

            $blueprint->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('institucional'); }
};