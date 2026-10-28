<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->timestamp('shipping_reviewed_at')->nullable()->after('shipping_profile')->index();
            $table->unsignedBigInteger('shipping_reviewed_by')->nullable()->after('shipping_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['shipping_reviewed_at']);
            $table->dropColumn(['shipping_reviewed_at', 'shipping_reviewed_by']);
        });
    }
};
