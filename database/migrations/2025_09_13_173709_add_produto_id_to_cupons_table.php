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
        Schema::table('cupons', function (Blueprint $table) {
            $table->unsignedBigInteger('produto_id')->nullable()->after('marca_id');
        });
    }
    
    public function down()
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn('produto_id');
        });
    }
    
};
