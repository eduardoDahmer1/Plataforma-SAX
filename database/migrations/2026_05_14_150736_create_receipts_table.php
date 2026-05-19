<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->timestamp('issued_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
