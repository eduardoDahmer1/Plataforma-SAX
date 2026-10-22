<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_ai_batches', fn (Blueprint $table) => $table->boolean('attributes_only')->default(false));
    }

    public function down(): void
    {
        Schema::table('product_ai_batches', fn (Blueprint $table) => $table->dropColumn('attributes_only'));
    }
};
