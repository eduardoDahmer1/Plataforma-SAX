<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNonOpticalProducts extends Command
{
    protected $signature = 'catalog:prune-non-optical {--force : Confirma a exclusão permanente}';
    protected $description = 'Remove do banco da Ótica produtos que não pertencem à categoria óptica';

    public function handle(): int
    {
        $database = DB::getDatabaseName();
        if (! in_array($database, config('catalog-sync.optical_database_names', []), true)) {
            $this->error("Limpeza recusada: {$database} não está configurado como banco exclusivo da Ótica.");
            return self::FAILURE;
        }

        $categoryIds = Category::query()
            ->where(function ($query): void {
                $query->where('ref_code', config('catalog-sync.optical_category_ref_code'))
                    ->orWhereIn(DB::raw('LOWER(slug)'), config('catalog-sync.optical_category_slugs', []));
            })
            ->pluck('id');

        if ($categoryIds->isEmpty()) {
            $this->error('Limpeza recusada: nenhuma categoria óptica foi encontrada.');
            return self::FAILURE;
        }

        $query = Product::withoutGlobalScopes()->where(function ($products) use ($categoryIds): void {
            $products->whereNull('category_id')->orWhereNotIn('category_id', $categoryIds);
        });
        $count = (clone $query)->count();
        $historicalItems = DB::table('order_items')->whereIn('product_id', (clone $query)->select('id'))->count();

        $this->table(['Banco', 'Categorias ópticas', 'Produtos a remover', 'Itens históricos preservados'], [[
            $database, $categoryIds->implode(', '), number_format($count, 0, ',', '.'), number_format($historicalItems, 0, ',', '.'),
        ]]);

        if (! $this->option('force')) {
            $this->warn('Prévia concluída. Execute novamente com --force após conferir os números.');
            return self::SUCCESS;
        }

        if (! $this->historyUsesSetNull()) {
            $this->error('Limpeza recusada: order_items.product_id ainda apaga itens em cascata. Execute as migrações primeiro.');
            return self::FAILURE;
        }

        $deleted = DB::transaction(fn () => $query->delete());
        $this->info(number_format($deleted, 0, ',', '.').' produtos não ópticos removidos; pedidos históricos foram preservados.');

        return self::SUCCESS;
    }

    private function historyUsesSetNull(): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return true;
        }

        $rule = DB::table('information_schema.REFERENTIAL_CONSTRAINTS as rc')
            ->join('information_schema.KEY_COLUMN_USAGE as k', function ($join): void {
                $join->on('k.CONSTRAINT_SCHEMA', '=', 'rc.CONSTRAINT_SCHEMA')
                    ->on('k.CONSTRAINT_NAME', '=', 'rc.CONSTRAINT_NAME');
            })
            ->where('rc.CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('k.TABLE_NAME', 'order_items')
            ->where('k.COLUMN_NAME', 'product_id')
            ->where('k.REFERENCED_TABLE_NAME', 'products')
            ->value('rc.DELETE_RULE');

        return strtoupper((string) $rule) === 'SET NULL';
    }
}
