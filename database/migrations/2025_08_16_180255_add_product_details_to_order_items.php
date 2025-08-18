<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('product_id');
            $table->string('external_name')->nullable()->after('name');
            $table->string('slug')->nullable()->after('external_name');
            $table->string('sku')->nullable()->after('slug');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'external_name', 'slug', 'sku']);
        });
    }
};
