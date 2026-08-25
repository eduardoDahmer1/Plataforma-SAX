<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_ai_preparations')) {
            return;
        }

        Schema::create('product_ai_preparations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->string('status', 20);
            $table->json('sources')->nullable();
            $table->string('confidence', 20)->nullable();
            $table->string('model')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ai_preparations');
    }
};
