<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_category_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->index()->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->index()->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->index()->constrained('subcategories')->nullOnDelete();
            $table->foreignId('childcategory_id')->nullable()->index()->constrained('childcategories')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_category_assignments');
    }
};
