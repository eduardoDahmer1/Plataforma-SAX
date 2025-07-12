<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('sign', 10);
            $table->double('value');
            $table->tinyInteger('is_default')->default(0);
            $table->char('decimal_separator', 1)->default('.');
            $table->char('thousands_separator', 1)->nullable();
            $table->integer('decimal_digits')->default(2);
            $table->string('description', 30);
            $table->timestamps();
            
            // Índices
            $table->index('name');
            $table->index('is_default');
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
