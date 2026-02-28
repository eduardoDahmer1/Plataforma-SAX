<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('logo_bridal')->nullable()->after('logo_palace');

        });
    }

    
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('logo_bridal');  
        });
    }
};
