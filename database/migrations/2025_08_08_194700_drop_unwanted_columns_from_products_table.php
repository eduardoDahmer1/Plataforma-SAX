<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUnwantedColumnsFromProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type',
                'affiliate_link',
                'attributes',
                'thumbnail',
                'file',
                'size',
                'size_qty',
                'size_price',
                'color',
                'previous_price',
                'views',
                'colors',
                'product_condition',
                'is_meta',
                'youtube',
                'link',
                'platform',
                'region',
                'licence_type',
                'measure',
                'featured',
                'best',
                'top',
                'hot',
                'latest',
                'big',
                'trending',
                'sale',
                'is_discount',
                'discount_date',
                'whole_sell_qty',
                'whole_sell_discount',
                'is_catalog',
                'catalog_id',
                'mpn',
                'free_shipping',
                'weight',
                'width',
                'height',
                'length',
                'ftp_hash',
                'color_qty',
                'color_price',
                'being_sold',
                'color_gallery',
                'material',
                'material_gallery',
                'material_qty',
                'material_price',
                'mercadolivre_name',
                'mercadolivre_description',
                'mercadolivre_id',
                'mercadolivre_category_attributes',
                'mercadolivre_listing_type_id',
                'mercadolivre_price',
                'mercadolivre_warranty_type_id',
                'mercadolivre_warranty_type_name',
                'mercadolivre_warranty_time',
                'mercadolivre_warranty_time_unit',
                'mercadolivre_without_warranty',
                'show_in_navbar',
                'product_size',
                'gtin',
                'promotion_price',
            ]);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['normal', 'affiliate'])->default('normal');
            $table->text('affiliate_link')->nullable();
            $table->text('attributes')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('file', 191)->nullable();
            $table->string('size', 191)->nullable();
            $table->string('size_qty', 191)->nullable();
            $table->string('size_price', 191)->nullable();
            $table->text('color')->nullable();
            $table->double('previous_price')->nullable();
            $table->integer('views')->unsigned()->default(0);
            $table->text('colors')->nullable();
            $table->tinyInteger('product_condition')->default(0);
            $table->tinyInteger('is_meta')->default(0);
            $table->string('youtube', 191)->nullable();
            $table->text('link')->nullable();
            $table->string('platform')->nullable();
            $table->string('region')->nullable();
            $table->string('licence_type')->nullable();
            $table->string('measure', 191)->nullable();
            $table->tinyInteger('featured')->unsigned()->default(0);
            $table->tinyInteger('best')->unsigned()->default(0);
            $table->tinyInteger('top')->unsigned()->default(0);
            $table->tinyInteger('hot')->unsigned()->default(0);
            $table->tinyInteger('latest')->unsigned()->default(0);
            $table->tinyInteger('big')->unsigned()->default(0);
            $table->tinyInteger('trending')->default(0);
            $table->tinyInteger('sale')->default(0);
            $table->tinyInteger('is_discount')->default(0);
            $table->text('discount_date')->nullable();
            $table->text('whole_sell_qty')->nullable();
            $table->text('whole_sell_discount')->nullable();
            $table->tinyInteger('is_catalog')->default(0);
            $table->unsignedBigInteger('catalog_id')->default(0);
            $table->string('mpn', 50)->nullable();
            $table->tinyInteger('free_shipping')->nullable();
            $table->double('weight')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('length')->nullable();
            $table->string('ftp_hash')->nullable();
            $table->string('color_qty')->nullable();
            $table->string('color_price')->nullable();
            $table->tinyInteger('being_sold')->default(0);
            $table->mediumText('color_gallery')->nullable();
            $table->string('material')->nullable();
            $table->mediumText('material_gallery')->nullable();
            $table->string('material_qty')->nullable();
            $table->string('material_price')->nullable();
            $table->string('mercadolivre_name')->nullable();
            $table->text('mercadolivre_description')->nullable();
            $table->string('mercadolivre_id')->nullable();
            $table->text('mercadolivre_category_attributes')->nullable();
            $table->string('mercadolivre_listing_type_id')->nullable();
            $table->decimal('mercadolivre_price', 10, 2)->nullable();
            $table->string('mercadolivre_warranty_type_id')->nullable();
            $table->string('mercadolivre_warranty_type_name')->nullable();
            $table->string('mercadolivre_warranty_time')->nullable();
            $table->string('mercadolivre_warranty_time_unit')->nullable();
            $table->tinyInteger('mercadolivre_without_warranty')->default(0);
            $table->tinyInteger('show_in_navbar')->unsigned()->default(0);
            $table->string('product_size')->nullable();
            $table->string('gtin')->nullable();
            $table->double('promotion_price')->nullable();
        });
    }
}
