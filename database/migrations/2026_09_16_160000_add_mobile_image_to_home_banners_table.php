<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('home_banners') && ! Schema::hasColumn('home_banners', 'mobile_image')) {
            Schema::table('home_banners', function (Blueprint $table) {
                $table->string('mobile_image')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('home_banners') && Schema::hasColumn('home_banners', 'mobile_image')) {
            Schema::table('home_banners', function (Blueprint $table) {
                $table->dropColumn('mobile_image');
            });
        }
    }
};
