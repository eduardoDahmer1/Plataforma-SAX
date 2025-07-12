<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildcategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('childcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategory_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('childcategories');
    }
}
