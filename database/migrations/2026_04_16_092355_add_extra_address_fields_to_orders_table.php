<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Adicionando campos que faltam para o endereço principal da Order
            if (!Schema::hasColumn('orders', 'complement')) {
                $table->string('complement')->nullable()->after('number');
            }
            if (!Schema::hasColumn('orders', 'district')) {
                $table->string('district')->nullable()->after('complement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['complement', 'district']);
        });
    }
};