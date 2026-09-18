<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('product_category_assignments', 'product_additional_categories');

        Schema::table('product_additional_categories', function (Blueprint $table): void {
            $table->index(['category_id', 'product_id'], 'product_additional_categories_category_product_index');
            $table->index(['subcategory_id', 'product_id'], 'product_additional_categories_subcategory_product_index');
            $table->index(['childcategory_id', 'product_id'], 'product_additional_categories_childcategory_product_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_additional_categories', function (Blueprint $table): void {
            $table->dropIndex('product_additional_categories_category_product_index');
            $table->dropIndex('product_additional_categories_subcategory_product_index');
            $table->dropIndex('product_additional_categories_childcategory_product_index');
        });

        Schema::rename('product_additional_categories', 'product_category_assignments');
    }
};
