<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoimageToAttributesTable extends Migration
{
    public function up()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('noimage')->nullable();
        });
    }

    public function down()
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('noimage');
        });
    }
}
