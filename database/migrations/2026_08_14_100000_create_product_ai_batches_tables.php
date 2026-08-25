<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_ai_jobs')) {
            Schema::create('product_ai_jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('product_ai_batches')) {
            Schema::create('product_ai_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('source_type', 20);
                $table->string('original_filename')->nullable();
                $table->string('status', 20)->default('draft')->index();
                $table->unsignedSmallInteger('input_count')->default(0);
                $table->unsignedSmallInteger('duplicate_count')->default(0);
                $table->unsignedSmallInteger('eligible_count')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_ai_batch_items')) {
            Schema::create('product_ai_batch_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('product_ai_batches')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('submitted_sku')->nullable();
                $table->json('source_skus')->nullable();
                $table->json('target_product_ids')->nullable();
                $table->string('status', 20)->index();
                $table->text('message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();

                $table->index(['batch_id', 'status']);
                $table->unique(['batch_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ai_batch_items');
        Schema::dropIfExists('product_ai_batches');
        Schema::dropIfExists('product_ai_jobs');
    }
};
