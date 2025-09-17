<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->date('data_inicio');
            $table->date('data_final');
            $table->decimal('valor_minimo', 10, 2)->nullable();
            $table->decimal('valor_maximo', 10, 2)->nullable();
            $table->enum('tipo', ['percentual', 'valor_fixo']); // ex: desconto %
            $table->decimal('montante', 10, 2); // valor ou %
            $table->integer('quantidade')->nullable(); // null = ilimitado
            $table->integer('usado')->default(0);
            $table->enum('modelo', ['categoria', 'produto', 'marca'])->nullable(); // tipo de desconto
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('marca_id')->nullable();
            $table->foreign('categoria_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('marca_id')->references('id')->on('brands')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupons');
    }
};
