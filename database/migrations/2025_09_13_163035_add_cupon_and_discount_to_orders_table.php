<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('cupon_id')->nullable()->after('total');
            $table->decimal('discount', 10, 2)->default(0)->after('cupon_id');

            $table->foreign('cupon_id')->references('id')->on('cupons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cupon_id']);
            $table->dropColumn(['cupon_id', 'discount']);
        });
    }
};
