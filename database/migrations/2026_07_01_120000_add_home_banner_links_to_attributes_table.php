<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->string("banner{$i}_link", 255)->nullable()->after("banner{$i}");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("banner{$i}_link");
            }
        });
    }
};
