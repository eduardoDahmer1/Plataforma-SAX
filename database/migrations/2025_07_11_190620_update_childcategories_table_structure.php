<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateChildcategoriesTableStructure extends Migration
{
    public function up()
    {
        Schema::table('childcategories', function (Blueprint $table) {
            if (!Schema::hasColumn('childcategories', 'subcategory_id')) {
                $table->unsignedBigInteger('subcategory_id')->index()->nullable(false);
            } else {
                $table->unsignedBigInteger('subcategory_id')->index()->change();
            }

            if (!Schema::hasColumn('childcategories', 'slug')) {
                $table->string('slug', 191)->nullable(false);
            } else {
                $table->string('slug', 191)->nullable(false)->change();
            }

            if (!Schema::hasColumn('childcategories', 'status')) {
                $table->tinyInteger('status')->default(1)->nullable(false);
            }

            if (!Schema::hasColumn('childcategories', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->index();
            } else {
                $table->unsignedBigInteger('category_id')->nullable()->index()->change();
            }

            if (!Schema::hasColumn('childcategories', 'ref_code')) {
                $table->string('ref_code', 50)->nullable()->index();
            }

            if (!Schema::hasColumn('childcategories', 'banner')) {
                $table->string('banner', 255)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('childcategories', function (Blueprint $table) {
            $table->dropColumn([
                'subcategory_id', 'slug', 'status', 'category_id', 'ref_code', 'banner'
            ]);
        });
    }
}
