<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 14, 2)->default(0);
            $table->unsignedInteger('items_count')->default(0);
            $table->string('currency_sign', 10)->default('US$');
            $table->decimal('currency_value', 14, 6)->default(1);
            $table->string('status', 20)->default('abandoned');
            $table->timestamp('abandoned_at');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'abandoned_at']);
            $table->index(['status', 'abandoned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
