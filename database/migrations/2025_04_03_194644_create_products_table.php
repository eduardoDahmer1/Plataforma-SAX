<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();  // Coluna id, tipo auto increment
            $table->string('sku');  // Coluna sku
            $table->string('name');  // Coluna name
            $table->string('categorie');  // Coluna categorie
            $table->string('subcategorie');  // Coluna subcategorie
            $table->string('daughter_category');  // Coluna daughter category
            $table->string('photo')->nullable();  // Coluna photo
            $table->json('galerie')->nullable();  // Coluna galerie, tipo JSON
            $table->text('description')->nullable();  // Coluna description
            $table->timestamps();  // Colunas created_at e updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
