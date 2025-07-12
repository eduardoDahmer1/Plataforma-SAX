<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('locale', 10);
            
            $table->string('name')->nullable();
            $table->text('details')->nullable();
            $table->text('ship')->nullable();
            $table->text('policy')->nullable();
            $table->text('meta_tag')->nullable();
            $table->text('features')->nullable();
            $table->text('tags')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'locale']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_translations');
    }
}
