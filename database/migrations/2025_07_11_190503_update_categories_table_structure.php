<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCategoriesTableStructure extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug', 191)->nullable(false);
            } else {
                $table->string('slug', 191)->nullable(false)->change();
            }

            if (!Schema::hasColumn('categories', 'status')) {
                $table->tinyInteger('status')->default(1)->nullable(false);
            }

            if (!Schema::hasColumn('categories', 'photo')) {
                $table->string('photo', 191)->nullable();
            }

            if (!Schema::hasColumn('categories', 'is_featured')) {
                $table->tinyInteger('is_featured')->default(0)->nullable(false);
            }

            if (!Schema::hasColumn('categories', 'image')) {
                $table->string('image', 191)->nullable();
            }

            if (!Schema::hasColumn('categories', 'is_customizable')) {
                $table->tinyInteger('is_customizable')->default(0)->nullable(false);
            }

            if (!Schema::hasColumn('categories', 'presentation_position')) {
                $table->integer('presentation_position')->default(0)->nullable(false);
            }

            if (!Schema::hasColumn('categories', 'ref_code')) {
                $table->string('ref_code', 50)->nullable()->index();
            }

            if (!Schema::hasColumn('categories', 'is_customizable_number')) {
                $table->string('is_customizable_number', 255)->nullable();
            }

            if (!Schema::hasColumn('categories', 'banner')) {
                $table->string('banner', 255)->nullable();
            }

            if (!Schema::hasColumn('categories', 'link')) {
                $table->string('link', 255)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'status', 'photo', 'is_featured', 'image', 'is_customizable',
                'presentation_position', 'ref_code', 'is_customizable_number', 'banner', 'link'
            ]);
        });
    }
}
