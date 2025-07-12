<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOldColumnsInProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'categorie')) {
                $table->renameColumn('categorie', 'category_id');
            }
            if (Schema::hasColumn('products', 'subcategorie')) {
                $table->renameColumn('subcategorie', 'subcategory_id');
            }
            if (Schema::hasColumn('products', 'daughter_category')) {
                $table->renameColumn('daughter_category', 'childcategory_id');
            }
            if (Schema::hasColumn('products', 'galerie')) {
                $table->renameColumn('galerie', 'gallery');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->renameColumn('category_id', 'categorie');
            }
            if (Schema::hasColumn('products', 'subcategory_id')) {
                $table->renameColumn('subcategory_id', 'subcategorie');
            }
            if (Schema::hasColumn('products', 'childcategory_id')) {
                $table->renameColumn('childcategory_id', 'daughter_category');
            }
            if (Schema::hasColumn('products', 'gallery')) {
                $table->renameColumn('gallery', 'galerie');
            }
        });
    }
}
