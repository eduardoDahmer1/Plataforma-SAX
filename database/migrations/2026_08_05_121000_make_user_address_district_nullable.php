<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_addresses', 'district')) {
            Schema::table('user_addresses', function (Blueprint $table): void {
                $table->string('district', 160)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_addresses', 'district')) {
            Schema::table('user_addresses', function (Blueprint $table): void {
                $table->string('district', 160)->nullable(false)->change();
            });
        }
    }
};
