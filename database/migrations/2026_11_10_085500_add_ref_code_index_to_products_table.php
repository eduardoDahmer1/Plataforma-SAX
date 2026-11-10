<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'products_ref_code_index';

    public function up(): void
    {
        if (! $this->indexExists()) {
            Schema::table('products', function (Blueprint $table): void {
                $table->index('ref_code', self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists()) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropIndex(self::INDEX);
            });
        }
    }

    private function indexExists(): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'products')
            ->where('index_name', self::INDEX)
            ->exists();
    }
};
