<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTableStructure extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Alterar tipo das colunas renomeadas (assumindo que já existem)
            if (Schema::hasColumn('products', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            }
            if (Schema::hasColumn('products', 'subcategory_id')) {
                $table->unsignedBigInteger('subcategory_id')->nullable()->change();
            }
            if (Schema::hasColumn('products', 'childcategory_id')) {
                $table->unsignedBigInteger('childcategory_id')->nullable()->change();
            }

            // Adicionar novos campos só se não existirem
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['normal', 'affiliate'])->default('normal');
            }
            if (!Schema::hasColumn('products', 'affiliate_link')) {
                $table->text('affiliate_link')->nullable();
            }
            if (!Schema::hasColumn('products', 'user_id')) {
                $table->unsignedBigInteger('user_id')->default(0);
            }
            if (!Schema::hasColumn('products', 'attributes')) {
                $table->text('attributes')->nullable();
            }
            if (!Schema::hasColumn('products', 'slug')) {
                $table->text('slug')->nullable();
            }
            if (!Schema::hasColumn('products', 'thumbnail')) {
                $table->string('thumbnail')->nullable();
            }
            if (!Schema::hasColumn('products', 'file')) {
                $table->string('file', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'size')) {
                $table->string('size', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'size_qty')) {
                $table->string('size_qty', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'size_price')) {
                $table->string('size_price', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'color')) {
                $table->text('color')->nullable();
            }
            if (!Schema::hasColumn('products', 'price')) {
                $table->double('price')->default(0);
            }
            if (!Schema::hasColumn('products', 'previous_price')) {
                $table->double('previous_price')->nullable();
            }
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->nullable();
            }
            if (!Schema::hasColumn('products', 'status')) {
                $table->tinyInteger('status')->unsigned()->default(1);
            }
            if (!Schema::hasColumn('products', 'views')) {
                $table->integer('views')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'colors')) {
                $table->text('colors')->nullable();
            }
            if (!Schema::hasColumn('products', 'product_condition')) {
                $table->tinyInteger('product_condition')->default(0);
            }
            if (!Schema::hasColumn('products', 'is_meta')) {
                $table->tinyInteger('is_meta')->default(0);
            }
            if (!Schema::hasColumn('products', 'youtube')) {
                $table->string('youtube', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'type')) {
                $table->enum('type', ['Physical', 'Digital', 'License'])->default('Physical');
            }
            if (!Schema::hasColumn('products', 'license')) {
                $table->text('license')->nullable();
            }
            if (!Schema::hasColumn('products', 'license_qty')) {
                $table->text('license_qty')->nullable();
            }
            if (!Schema::hasColumn('products', 'link')) {
                $table->text('link')->nullable();
            }
            if (!Schema::hasColumn('products', 'platform')) {
                $table->string('platform')->nullable();
            }
            if (!Schema::hasColumn('products', 'region')) {
                $table->string('region')->nullable();
            }
            if (!Schema::hasColumn('products', 'licence_type')) {
                $table->string('licence_type')->nullable();
            }
            if (!Schema::hasColumn('products', 'measure')) {
                $table->string('measure', 191)->nullable();
            }
            if (!Schema::hasColumn('products', 'featured')) {
                $table->tinyInteger('featured')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'best')) {
                $table->tinyInteger('best')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'top')) {
                $table->tinyInteger('top')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'hot')) {
                $table->tinyInteger('hot')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'latest')) {
                $table->tinyInteger('latest')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'big')) {
                $table->tinyInteger('big')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'trending')) {
                $table->tinyInteger('trending')->default(0);
            }
            if (!Schema::hasColumn('products', 'sale')) {
                $table->tinyInteger('sale')->default(0);
            }
            if (!Schema::hasColumn('products', 'is_discount')) {
                $table->tinyInteger('is_discount')->default(0);
            }
            if (!Schema::hasColumn('products', 'discount_date')) {
                $table->text('discount_date')->nullable();
            }
            if (!Schema::hasColumn('products', 'whole_sell_qty')) {
                $table->text('whole_sell_qty')->nullable();
            }
            if (!Schema::hasColumn('products', 'whole_sell_discount')) {
                $table->text('whole_sell_discount')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_catalog')) {
                $table->tinyInteger('is_catalog')->default(0);
            }
            if (!Schema::hasColumn('products', 'catalog_id')) {
                $table->unsignedBigInteger('catalog_id')->default(0);
            }
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'ref_code')) {
                $table->string('ref_code', 50)->nullable();
            }
            if (!Schema::hasColumn('products', 'ref_code_int')) {
                $table->unsignedInteger('ref_code_int')->nullable();
            }
            if (!Schema::hasColumn('products', 'mpn')) {
                $table->string('mpn', 50)->nullable();
            }
            if (!Schema::hasColumn('products', 'free_shipping')) {
                $table->tinyInteger('free_shipping')->nullable();
            }
            if (!Schema::hasColumn('products', 'max_quantity')) {
                $table->unsignedInteger('max_quantity')->nullable();
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->double('weight')->nullable();
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->integer('width')->nullable();
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->integer('height')->nullable();
            }
            if (!Schema::hasColumn('products', 'length')) {
                $table->integer('length')->nullable();
            }
            if (!Schema::hasColumn('products', 'external_name')) {
                $table->string('external_name')->nullable();
            }
            if (!Schema::hasColumn('products', 'ftp_hash')) {
                $table->string('ftp_hash')->nullable();
            }
            if (!Schema::hasColumn('products', 'color_qty')) {
                $table->string('color_qty')->nullable();
            }
            if (!Schema::hasColumn('products', 'color_price')) {
                $table->string('color_price')->nullable();
            }
            if (!Schema::hasColumn('products', 'being_sold')) {
                $table->tinyInteger('being_sold')->default(0);
            }
            if (!Schema::hasColumn('products', 'vendor_min_price')) {
                $table->double('vendor_min_price')->default(0);
            }
            if (!Schema::hasColumn('products', 'vendor_max_price')) {
                $table->double('vendor_max_price')->default(0);
            }
            if (!Schema::hasColumn('products', 'color_gallery')) {
                $table->mediumText('color_gallery')->nullable();
            }
            if (!Schema::hasColumn('products', 'material')) {
                $table->string('material')->nullable();
            }
            if (!Schema::hasColumn('products', 'material_gallery')) {
                $table->mediumText('material_gallery')->nullable();
            }
            if (!Schema::hasColumn('products', 'material_qty')) {
                $table->string('material_qty')->nullable();
            }
            if (!Schema::hasColumn('products', 'material_price')) {
                $table->string('material_price')->nullable();
            }
            if (!Schema::hasColumn('products', 'show_price')) {
                $table->boolean('show_price')->default(1);
            }
            if (!Schema::hasColumn('products', 'mercadolivre_name')) {
                $table->string('mercadolivre_name')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_description')) {
                $table->text('mercadolivre_description')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_id')) {
                $table->string('mercadolivre_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_category_attributes')) {
                $table->text('mercadolivre_category_attributes')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_listing_type_id')) {
                $table->string('mercadolivre_listing_type_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_price')) {
                $table->decimal('mercadolivre_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_warranty_type_id')) {
                $table->string('mercadolivre_warranty_type_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_warranty_type_name')) {
                $table->string('mercadolivre_warranty_type_name')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_warranty_time')) {
                $table->string('mercadolivre_warranty_time')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_warranty_time_unit')) {
                $table->string('mercadolivre_warranty_time_unit')->nullable();
            }
            if (!Schema::hasColumn('products', 'mercadolivre_without_warranty')) {
                $table->tinyInteger('mercadolivre_without_warranty')->default(0);
            }
            if (!Schema::hasColumn('products', 'show_in_navbar')) {
                $table->tinyInteger('show_in_navbar')->unsigned()->default(0);
            }
            if (!Schema::hasColumn('products', 'product_size')) {
                $table->string('product_size')->nullable();
            }
            if (!Schema::hasColumn('products', 'synced')) {
                $table->tinyInteger('synced')->default(0);
            }
            if (!Schema::hasColumn('products', 'gtin')) {
                $table->string('gtin')->nullable();
            }
            if (!Schema::hasColumn('products', 'promotion_price')) {
                $table->double('promotion_price')->nullable();
            }

            // // Índices
            // $table->index('category_id');
            // $table->index('subcategory_id');
            // $table->index('childcategory_id');
            // $table->index('brand_id');
            // $table->index('ref_code');
            // $table->index('ref_code_int');
            // $table->index('sku');
            // $table->index('synced');

            // Chaves estrangeiras
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null')->onUpdate('restrict');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('restrict');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null')->onUpdate('restrict');
            $table->foreign('childcategory_id')->references('id')->on('childcategories')->onDelete('set null')->onUpdate('restrict');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Remover chaves estrangeiras
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropForeign(['childcategory_id']);

            // Remover índices
            $table->dropIndex(['category_id']);
            $table->dropIndex(['subcategory_id']);
            $table->dropIndex(['childcategory_id']);
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['ref_code']);
            $table->dropIndex(['ref_code_int']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['synced']);

            // Você pode usar $table->dropColumn([...]) aqui se quiser remover as colunas também
        });
    }
}
