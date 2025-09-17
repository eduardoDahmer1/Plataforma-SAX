<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar colunas na tabela products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'previous_price')) {
                $table->decimal('previous_price', 10, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'promotion_price')) {
                $table->decimal('promotion_price', 10, 2)->default(0)->after('previous_price');
            }
            if (!Schema::hasColumn('products', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('products', 'attributes')) {
                $table->text('attributes')->nullable()->after('promotion_price');
            }
        });

        // Criar tabela galleries
        if (!Schema::hasTable('galleries')) {
            Schema::create('galleries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('photo')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        // Criar tabela pickup_product
        if (!Schema::hasTable('pickup_product')) {
            Schema::create('pickup_product', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('location')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'previous_price')) {
                $table->dropColumn('previous_price');
            }
            if (Schema::hasColumn('products', 'promotion_price')) {
                $table->dropColumn('promotion_price');
            }
            if (Schema::hasColumn('products', 'thumbnail')) {
                $table->dropColumn('thumbnail');
            }
            if (Schema::hasColumn('products', 'attributes')) {
                $table->dropColumn('attributes');
            }
        });

        Schema::dropIfExists('galleries');
        Schema::dropIfExists('pickup_product');
    }
};
