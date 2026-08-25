<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_ai_batch_items') && ! Schema::hasColumn('product_ai_batch_items', 'target_product_ids')) {
            Schema::table('product_ai_batch_items', function (Blueprint $table) {
                $table->json('target_product_ids')->nullable()->after('source_skus');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_ai_batch_items') && Schema::hasColumn('product_ai_batch_items', 'target_product_ids')) {
            Schema::table('product_ai_batch_items', function (Blueprint $table) {
                $table->dropColumn('target_product_ids');
            });
        }
    }
};
