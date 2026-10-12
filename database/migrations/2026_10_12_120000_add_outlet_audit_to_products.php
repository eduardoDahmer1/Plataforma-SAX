<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('outlet_at')->nullable()->after('status_before_outlet')->index();
            $table->foreignId('outlet_by')->nullable()->after('outlet_at')
                ->constrained('users')->nullOnDelete();
        });

        Schema::create('product_outlet_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sku', 191)->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('action', 20)->index();
            $table->boolean('previous_status')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['action', 'created_at'], 'product_outlet_events_action_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_outlet_events');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_by');
            $table->dropIndex(['outlet_at']);
            $table->dropColumn('outlet_at');
        });
    }
};
