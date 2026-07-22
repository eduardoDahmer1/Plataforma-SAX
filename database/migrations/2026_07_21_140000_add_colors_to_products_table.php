<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'colors')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('colors')->nullable()->after('color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'colors')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('colors');
            });
        }
    }
};
