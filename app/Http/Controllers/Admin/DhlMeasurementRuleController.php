<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DhlMeasurementRule;
use App\Services\Dhl\DhlMeasurementRuleDefaults;
use App\Services\Dhl\DhlMeasurementRuleResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DhlMeasurementRuleController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureMasterAdmin();

        $query = DhlMeasurementRule::query()->orderByRaw("FIELD(level, 'category', 'subcategory', 'childcategory')")->orderBy('scope_name');
        if ($search = trim((string) $request->query('q'))) {
            $query->where('scope_name', 'like', '%'.$search.'%');
        }
        if (in_array($request->query('level'), ['category', 'subcategory', 'childcategory'], true)) {
            $query->where('level', $request->query('level'));
        }
        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        } elseif ($request->query('status') === 'manual') {
            $query->where('requires_manual_review', true);
        }

        $rules = $query->paginate(40)->withQueryString();
        $productCounts = $this->productCounts($rules->items());

        return view('admin.dhl.measurements', [
            'rules' => $rules,
            'productCounts' => $productCounts,
            'stats' => [
                'total' => DhlMeasurementRule::query()->count(),
                'active' => DhlMeasurementRule::query()->where('active', true)->count(),
                'manual' => DhlMeasurementRule::query()->where('requires_manual_review', true)->count(),
                'exact' => DB::table('products')
                    ->where('shipping_weight_kg', '>', 0)
                    ->where('shipping_length_cm', '>', 0)
                    ->where('shipping_width_cm', '>', 0)
                    ->where('shipping_height_cm', '>', 0)
                    ->count(),
            ],
        ]);
    }

    public function update(Request $request, DhlMeasurementRule $measurementRule, DhlMeasurementRuleResolver $resolver): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $data = $request->validate([
            'active' => ['nullable', 'boolean'],
            'requires_manual_review' => ['nullable', 'boolean'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'length_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'width_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'height_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
        ]);
        $data['active'] = $request->boolean('active');
        $data['requires_manual_review'] = $request->boolean('requires_manual_review');

        $measurementRule->update($data);
        $resolver->clearCache();

        return back()->with('success', 'Medidas médias atualizadas para '.$measurementRule->scope_name.'.');
    }

    public function sync(DhlMeasurementRuleDefaults $defaults, DhlMeasurementRuleResolver $resolver): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $created = $defaults->sync();
        $resolver->clearCache();

        return back()->with('success', $created > 0
            ? "{$created} nova(s) regra(s) criada(s) para a hierarquia atual."
            : 'Todas as categorias que possuem produtos já estão cadastradas.');
    }

    private function productCounts(array $rules): array
    {
        $collection = collect($rules);
        $counts = [];
        foreach ([
            'category' => 'category_id',
            'subcategory' => 'subcategory_id',
            'childcategory' => 'childcategory_id',
        ] as $level => $column) {
            $ids = $collection->where('level', $level)->pluck($column)->filter()->unique()->values();
            if ($ids->isEmpty()) {
                continue;
            }
            DB::table('products')->whereIn($column, $ids)->select($column, DB::raw('COUNT(*) as aggregate'))
                ->groupBy($column)->get()->each(function ($row) use (&$counts, $level, $column): void {
                    $counts[$level.':'.$row->{$column}] = (int) $row->aggregate;
                });
        }

        return $counts;
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }
}
