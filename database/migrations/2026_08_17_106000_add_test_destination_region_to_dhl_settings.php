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
            $table->string('test_destination_province_code', 20)->nullable()->after('test_destination_country_code');
            $table->string('test_destination_province_name', 120)->nullable()->after('test_destination_province_code');
        });

        DB::table('dhl_settings')
            ->where('test_destination_country_code', 'US')
            ->where('test_destination_city', 'New York')
            ->update([
                'test_destination_province_code' => 'NY',
                'test_destination_province_name' => 'New York',
            ]);
    }

    public function down(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'test_destination_province_code',
                'test_destination_province_name',
            ]);
        });
    }
};
