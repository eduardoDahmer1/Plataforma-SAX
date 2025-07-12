<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBrandsTableStructure extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            // Adiciona ou altera as colunas conforme a estrutura desejada
            if (!Schema::hasColumn('brands', 'ref_code')) {
                $table->string('ref_code', 255)->nullable()->index();
            }
            if (!Schema::hasColumn('brands', 'name')) {
                $table->string('name', 255)->nullable(false)->change();
            } else {
                // Garante que 'name' não seja nulo
                $table->string('name', 255)->nullable(false)->change();
            }
            if (!Schema::hasColumn('brands', 'slug')) {
                $table->string('slug', 255)->nullable(false);
            }
            if (!Schema::hasColumn('brands', 'image')) {
                $table->string('image', 255)->nullable();
            }
            if (!Schema::hasColumn('brands', 'status')) {
                $table->integer('status')->default(1)->nullable(false);
            }
            if (!Schema::hasColumn('brands', 'partner')) {
                $table->integer('partner')->nullable();
            }
            if (!Schema::hasColumn('brands', 'thumbnail')) {
                $table->string('thumbnail', 255)->nullable();
            }
            if (!Schema::hasColumn('brands', 'banner')) {
                $table->string('banner', 255)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn([
                'ref_code', 'slug', 'image', 'status', 'partner', 'thumbnail', 'banner'
            ]);
            // Se precisar, você pode alterar o campo 'name' para ser nullable de novo:
            // $table->string('name', 255)->nullable()->change();
        });
    }
}
