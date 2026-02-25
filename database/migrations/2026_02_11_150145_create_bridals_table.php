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
            $table->boolean('is_active')->default(true);

            // ========================================
            // SEO
            // ========================================
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // ========================================
            // SECCIÓN 01: HERO
            // ========================================
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_image')->nullable();

            // ========================================
            // SECCIÓN 02: BRAND TICKER (marcas)
            // ========================================
            // formato: [{"nombre":"ELIE SAAB","logo_imagen":null}, ...]
            $table->json('brands')->nullable();

            // ========================================
            // SECCIÓN 03: PROMO CAROUSEL
            // ========================================
            // formato: [{"image":"url","title":"texto","subtitle":"texto","button":"texto","link":"url"}, ...]
            $table->json('promos')->nullable();

            // ========================================
            // SECCIÓN 04: SERVICIOS
            // ========================================
            $table->string('services_label')->nullable();
            $table->string('services_title')->nullable();
            $table->string('services_cta_text')->nullable();
            $table->string('services_cta_link')->nullable();
            // formato: [{"image":"path/imagen","title":"texto","description":"texto"}, ...]
            $table->json('services')->nullable();

            // ========================================
            // SECCIÓN 05: PALACE BANNER
            // ========================================
            $table->string('palace_image')->nullable();
            $table->string('palace_subtitle')->nullable();
            $table->string('palace_title')->nullable();
            $table->text('palace_description')->nullable();
            $table->string('palace_link')->nullable();

            // ========================================
            // SECCIÓN 06: TESTIMONIOS
            // ========================================
            $table->string('testimonials_label')->nullable();
            $table->string('testimonials_title')->nullable();
            // formato: [{"quote":"texto","author":"nombre","foto":"url","ubicacion":"ciudad"}, ...]
            $table->json('testimonials')->nullable();

            // ========================================
            // SECCIÓN 07: INSTAGRAM CTA
            // ========================================
            $table->string('social_instagram')->nullable();

            // ========================================
            // SECCIÓN 08: CONTACTO / SUCURSALES
            // ========================================
            $table->string('branch_asuncion_name')->nullable();
            $table->string('branch_asuncion_address')->nullable();
            $table->string('branch_asuncion_phone')->nullable();
            $table->string('branch_asuncion_image')->nullable();

            $table->string('branch_cde_name')->nullable();
            $table->string('branch_cde_address')->nullable();
            $table->string('branch_cde_phone')->nullable();
            $table->string('branch_cde_image')->nullable();

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
