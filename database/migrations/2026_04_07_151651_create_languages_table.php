<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ex: 'vendas', 'sobre_nos'
            $table->text('pt')->nullable();  // Tradução em Português
            $table->text('en')->nullable();  // Tradução em Inglês
            $table->text('es')->nullable();  // Tradução em Espanhol
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};