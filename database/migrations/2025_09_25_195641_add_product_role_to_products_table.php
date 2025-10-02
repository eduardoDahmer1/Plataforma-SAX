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
            // Cria a coluna product_role com valores P ou F
            $table->enum('product_role', ['P', 'F'])
                  ->nullable()
                  ->after('parent_id')
                  ->comment('P = Produto Pai, F = Produto Filho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_role');
        });
    }
};
