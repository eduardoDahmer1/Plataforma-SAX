<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'phone_country')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone_country', 6)->nullable()->change();
            });
        }

        if (Schema::hasColumn('orders', 'phone_country')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('phone_country', 6)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'phone_country')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone_country', 3)->nullable()->change();
            });
        }

        if (Schema::hasColumn('orders', 'phone_country')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('phone_country', 3)->nullable()->change();
            });
        }
    }
};
