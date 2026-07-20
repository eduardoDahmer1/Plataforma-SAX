<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_outlet')->default(false)->index()->after('status');
            $table->boolean('status_before_outlet')->nullable()->after('is_outlet');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_outlet']);
            $table->dropColumn(['is_outlet', 'status_before_outlet']);
        });
    }
};
