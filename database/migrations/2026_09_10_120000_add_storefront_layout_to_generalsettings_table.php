<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->string('storefront_layout', 40)->default('sax')->after('site_name');
        });

        $profile = Schema::hasTable('system_settings')
            ? DB::table('system_settings')->value('store_profile')
            : null;

        if ($profile === 'otica') {
            DB::table('generalsettings')->update(['storefront_layout' => 'vista']);
        }
    }

    public function down(): void
    {
        Schema::table('generalsettings', function (Blueprint $table) {
            $table->dropColumn('storefront_layout');
        });
    }
};
