<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('banner6')->nullable()->after('banner5');
            $table->string('banner7')->nullable()->after('banner6');
            $table->string('banner8')->nullable()->after('banner7');
            $table->string('banner9')->nullable()->after('banner8');
            $table->string('banner10')->nullable()->after('banner9');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn(['banner6', 'banner7', 'banner8', 'banner9', 'banner10']);
        });
    }
};
