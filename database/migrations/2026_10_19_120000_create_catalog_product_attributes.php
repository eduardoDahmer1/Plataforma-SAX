<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('data_type', 16);
            $table->string('unit', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('inherit_from_parent')->default(false);
            $table->uuid('revision');
            $table->timestamps(6);
        });
        Schema::create('catalog_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('catalog_attributes')->restrictOnDelete();
            $table->string('code', 80);
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->uuid('revision');
            $table->timestamps(6);
            $table->unique(['attribute_id', 'code'], 'catalog_option_code_unique');
        });
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('catalog_attributes')->restrictOnDelete();
            // These are inheritance operations, not publication/review states. Keep
            // inherit rows as tombstones so a reset also propagates to the peer.
            $table->string('mode', 12)->default('value');
            $table->boolean('boolean_value')->nullable();
            $table->decimal('number_value', 16, 6)->nullable();
            $table->string('source', 12)->default('manual');
            $table->json('evidence')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('revision');
            $table->timestamps(6);
            $table->unique(['product_id', 'attribute_id'], 'product_attribute_unique');
            $table->index(['attribute_id', 'number_value'], 'product_attribute_number');
            $table->index(['attribute_id', 'boolean_value'], 'product_attribute_boolean');
        });
        Schema::create('product_attribute_value_options', function (Blueprint $table) {
            $table->foreignId('value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('catalog_attribute_options')->restrictOnDelete();
            $table->primary(['value_id', 'option_id']);
            $table->index(['option_id', 'value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_options');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('catalog_attribute_options');
        Schema::dropIfExists('catalog_attributes');
    }
};
