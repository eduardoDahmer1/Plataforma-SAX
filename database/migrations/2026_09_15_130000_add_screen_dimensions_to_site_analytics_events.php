<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_analytics_events')) {
            return;
        }

        Schema::table('site_analytics_events', function (Blueprint $table) {
            $table->unsignedSmallInteger('screen_width')->nullable()->after('device_type');
            $table->unsignedSmallInteger('screen_height')->nullable()->after('screen_width');
            $table->unsignedSmallInteger('viewport_width')->nullable()->after('screen_height');
            $table->unsignedSmallInteger('viewport_height')->nullable()->after('viewport_width');
            $table->index(['event_type', 'event_date', 'device_type'], 'analytics_event_date_device_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_analytics_events')) {
            return;
        }

        Schema::table('site_analytics_events', function (Blueprint $table) {
            $table->dropIndex('analytics_event_date_device_idx');
            $table->dropColumn(['screen_width', 'screen_height', 'viewport_width', 'viewport_height']);
        });
    }
};
