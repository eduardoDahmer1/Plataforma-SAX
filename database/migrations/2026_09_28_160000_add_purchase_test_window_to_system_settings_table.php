<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->timestamp('purchase_test_until')->nullable()->after('geonames_enabled');
            $table->unsignedBigInteger('purchase_test_activated_by')->nullable()->after('purchase_test_until');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('purchase_test_until');
        });
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('purchase_test_activated_by');
        });
    }
};
