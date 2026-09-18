<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'subcategories', 'childcategories'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'banner')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->string('banner', 255)->nullable()->after('photo');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['categories', 'subcategories', 'childcategories'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'banner')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('banner'));
            }
        }
    }
};
