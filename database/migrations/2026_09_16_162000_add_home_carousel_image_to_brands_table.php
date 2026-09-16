<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brands') && ! Schema::hasColumn('brands', 'home_carousel_image')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->string('home_carousel_image')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'home_carousel_image')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('home_carousel_image');
            });
        }
    }
};
