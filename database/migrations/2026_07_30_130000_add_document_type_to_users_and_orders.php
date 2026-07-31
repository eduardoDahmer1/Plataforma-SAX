<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('document_type', 20)->nullable()->after('document');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('document_type', 20)->nullable()->after('document');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
