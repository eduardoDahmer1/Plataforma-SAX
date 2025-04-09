<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDescriptionTypeInUploadsTable extends Migration
{
    public function up()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
}
