<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Coluna parent_id já existente
            $table->unsignedBigInteger('parent_id')
                  ->nullable()
                  ->after('brand_id');

            // Coluna para múltiplos produtos pai por cor
            $table->json('color_parent_id')
                  ->nullable()
                  ->after('parent_id');

            // Se quiser FK para parent_id:
            // $table->foreign('parent_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Dropar FK se você ativou
            // $table->dropForeign(['parent_id']);
            $table->dropColumn('color_parent_id');
            $table->dropColumn('parent_id');
        });
    }
};
