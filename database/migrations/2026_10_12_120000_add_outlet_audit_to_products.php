<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL aplica ALTER TABLE fora de uma transação. Se o processo for
        // interrompido, parte da estrutura pode existir sem que a migração
        // tenha sido registrada. Faça cada etapa de forma retomável.
        if (! Schema::hasColumn('products', 'outlet_at')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->timestamp('outlet_at')->nullable()->after('status_before_outlet');
            });
        }

        if (! Schema::hasIndex('products', ['outlet_at'])) {
            Schema::table('products', function (Blueprint $table): void {
                $table->index('outlet_at');
            });
        }

        if (! Schema::hasColumn('products', 'outlet_by')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->unsignedBigInteger('outlet_by')->nullable()->after('outlet_at');
            });
        }

        if (! $this->hasForeignKey('products', 'outlet_by')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreign('outlet_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('product_outlet_events')) {
            Schema::create('product_outlet_events', function (Blueprint $table): void {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('product_outlet_events');

        if (Schema::hasColumn('products', 'outlet_by')) {
            Schema::table('products', function (Blueprint $table): void {
                if ($this->hasForeignKey('products', 'outlet_by')) {
                    $table->dropForeign(['outlet_by']);
                }
                $table->dropColumn('outlet_by');
            });
        }

        if (Schema::hasColumn('products', 'outlet_at')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('outlet_at');
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, (array) ($foreignKey['columns'] ?? []), true)) {
                return true;
            }
        }

        return false;
    }
};
