<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderItemsTableAddFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('external_name')->nullable()->change();
            $table->string('slug')->nullable()->change();
            // A linha abaixo foi comentada pois a coluna 'sku' já existe no banco
            // $table->string('sku')->nullable()->after('slug'); 
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->integer('quantity')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('external_name')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            // Comentado para evitar erro ao reverter
            // $table->dropColumn('sku'); 
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('quantity')->default(0)->change();
        });
    }
}