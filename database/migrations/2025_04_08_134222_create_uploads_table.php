<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();                 // Título do conteúdo
            $table->text('description')->nullable();             // Descrição opcional
            $table->string('file_type');                         // Tipo do arquivo: image, video, document, etc.
            $table->string('file_path');                         // Caminho para o arquivo (armazenado em /storage ou S3)
            $table->string('original_name')->nullable();         // Nome original do arquivo
            $table->string('mime_type')->nullable();             // Tipo MIME
            $table->unsignedBigInteger('user_id')->nullable();   // Relacionado ao usuário que fez o upload (opcional)
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
