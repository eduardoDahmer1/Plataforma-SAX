<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_translations', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira ligando à tabela principal de blogs
            $table->foreignId('blog_id')->constrained('blogs')->onDelete('cascade');
            
            // Campo de idioma
            $table->string('locale')->index();

            // Campos que serão traduzidos dinamicamente no Blog
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('meta_tag')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            // Garante unicidade por idioma e post de blog
            $table->unique(['blog_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
    }
};