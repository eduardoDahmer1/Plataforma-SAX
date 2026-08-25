<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_analytics_events', function (Blueprint $table): void {
            $table->string('country_code', 2)->nullable()->after('referrer_host');
            $table->string('country_name', 80)->nullable()->after('country_code');
            $table->string('country_source', 20)->nullable()->after('country_name');
            $table->index(['event_type', 'event_date', 'country_code'], 'analytics_event_date_country_idx');
        });
    }

    public function down(): void
    {
        Schema::table('site_analytics_events', function (Blueprint $table): void {
            $table->dropIndex('analytics_event_date_country_idx');
            $table->dropColumn(['country_code', 'country_name', 'country_source']);
        });
    }
};
