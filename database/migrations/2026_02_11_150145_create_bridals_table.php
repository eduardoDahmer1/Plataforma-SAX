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
        Schema::create('bridals', function (Blueprint $table) {
           $table->id();
            
            // ========================================
            // CAMPOS BÁSICOS DE PÁGINA
            // ========================================
            $table->string('title')->default('SAX Bridal');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // ========================================
            // SEO (Muy importante!)
            // ========================================
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable(); // Para compartir en redes sociales
            
            // ========================================
            // SECCIÓN 01: HERO / Slider principal
            // ========================================
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable(); // Ruta: "bridal/hero/imagen.jpg"
            $table->string('hero_button_text')->nullable();
            $table->string('hero_button_link')->nullable();
            
            // ========================================
            // SECCIÓN 02: BANNERS / CTA
            // ========================================
            $table->json('banners')->nullable(); 
            $table->json('gallery_images')->nullable();

            // formato: [{"image":"path","title":"texto","link":"url"}, ...]
            
            // ========================================
            // SECCIÓN 03: INTRO / Presentación
            // ========================================
            $table->string('intro_title')->nullable();
            $table->text('intro_text')->nullable();
            
            // ========================================
            // SECCIÓN 04: Servicios
            // ========================================
            $table->json('services')->nullable(); 
            // formato: [{"icon":"icono","title":"texto","description":"texto"}, ...]
            
            // ========================================
            // SECCIÓN 05: Testimonios / Novias Reales
            // ========================================
            $table->json('testimonials')->nullable(); 
            // formato: [{"name":"nombre","surname":"apellido","photo":"path","date":"Y-m-d","message":"texto"}]
            
            // ========================================
            // SECCIÓN 06: Blog / Noticias
            // ========================================
            $table->json('blog_posts')->nullable();
            // formato: [{"title":"titulo","excerpt":"resumen","image":"path","link":"url"}]
            
            // ========================================
            // SECCIÓN 07: Frase / Quote
            // ========================================
            $table->string('quote')->nullable();
            $table->string('quote_author')->nullable(); 
            
            // ========================================
            // SECCIÓN 08: Contacto / Localización
            // ========================================
            $table->string('contact_address')->nullable();
            $table->string('contact_phone')->nullable(); 
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('contact_map_iframe')->nullable();
            
            // ========================================
            // REDES SOCIALES (importante para bridal!)
            // ========================================
            $table->string('social_instagram')->nullable();
            $table->string('social_facebook')->nullable();
            
            
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bridals');
    }
};
