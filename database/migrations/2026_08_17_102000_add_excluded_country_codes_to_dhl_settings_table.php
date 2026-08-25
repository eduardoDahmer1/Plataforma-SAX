<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->json('excluded_country_codes')->nullable()->after('incoterm');
        });

        DB::table('dhl_settings')
            ->whereNull('excluded_country_codes')
            ->update(['excluded_country_codes' => json_encode(['PY', 'BR'])]);
    }

    public function down(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->dropColumn('excluded_country_codes');
        });
    }
};
