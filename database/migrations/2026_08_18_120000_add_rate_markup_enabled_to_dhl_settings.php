<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->boolean('rate_markup_enabled')->default(true)->after('rate_markup_percent');
        });
    }

    public function down(): void
    {
        Schema::table('dhl_settings', function (Blueprint $table): void {
            $table->dropColumn('rate_markup_enabled');
        });
    }
};
