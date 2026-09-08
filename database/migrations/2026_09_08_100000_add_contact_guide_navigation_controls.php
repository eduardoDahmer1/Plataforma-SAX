<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_settings', 'header_guide_enabled')) {
                $table->boolean('header_guide_enabled')->default(true);
            }
            if (! Schema::hasColumn('system_settings', 'footer_guide_enabled')) {
                $table->boolean('footer_guide_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('system_settings', 'header_guide_enabled')) {
                $table->dropColumn('header_guide_enabled');
            }
            if (Schema::hasColumn('system_settings', 'footer_guide_enabled')) {
                $table->dropColumn('footer_guide_enabled');
            }
        });
    }
};
