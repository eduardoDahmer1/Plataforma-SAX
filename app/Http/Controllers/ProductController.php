<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Generalsetting;
use App\Models\Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{
    private function activeBase()
    {
        return Product::where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '');
    } 

    private function withCupons($query)
    {
        return $query->with(['cupons' => fn($q) => $q->ativos()]);
    }

    public function show($id_or_slug)
    {
        $product = Product::where('id', $id_or_slug)
            ->orWhere('slug', $id_or_slug)
            ->with(['brand', 'category', 'subcategory', 'categoriasfilhas'])
            ->firstOrFail();

        $sessionKey = 'viewed_product_' . $product->id;
        if (!Session::has($sessionKey)) {
            $product->increment('views');
            Session::put($sessionKey, true);
        }

        if (auth()->check()) {
            \DB::table('product_views_history')->updateOrInsert(
                ['user_id' => auth()->id(), 'product_id' => $product->id],
                ['updated_at' => now(), 'created_at' => now()]
            );
            Cache::forget('user_history_' . auth()->id());
        }

        $bridalBrandIds = [641, 610, 664, 1236, 1237, 1444, 951];
        $isBridal = in_array($product->brand_id, $bridalBrandIds)
            || ($product->category && str_contains(strtolower($product->category->name), 'bridal'));

        $product->current_price = $product->promotion_price > 0 ? $product->promotion_price : $product->price;
        $product->has_discount  = $product->previous_price > $product->current_price;

        $masterId = (int) $product->id;
        if ($product->product_role === 'F' && !empty($product->parent_id)) {
            $masterId = str_contains((string) $product->parent_id, ',')
                ? (int) (array_values(array_filter(array_map('trim', explode(',', $product->parent_id))))[0] ?? $masterId)
                : (int) $product->parent_id;
        }

        $siblings = Product::where(fn($q) => $q->where('parent_id', $masterId)->orWhere('id', $masterId))
            ->where('status', 1)
            ->get()
            ->sortBy(fn($s) => $this->sizeWeight($s->size))
            ->values();

        $colorGroupId    = !empty($product->color_parent_id) ? (int) $product->color_parent_id : (int) $product->id;
        $coresRelacionadas = Product::where(fn($q) => $q->where('color_parent_id', $colorGroupId)->orWhere('id', $colorGroupId))
            ->where('status', 1)
            ->where('product_role', 'P')
            ->get();

        $attribute  = Cache::remember('system_attributes', 600, fn() => Attribute::first());
        $settings   = Cache::remember('general_settings',  600, fn() => Generalsetting::first());
        $similares  = $this->getSimilares($product);
        $mostViewed = Cache::remember('show_most_viewed_products', 600,
            fn() => $this->activeBase()->with('brand')->orderBy('views', 'desc')->take(12)->get()
        );

        return view('produtos.show', [
            'product'           => $product,
            'isBridal'          => $isBridal,
            'siblings'          => $siblings,
            'coresRelacionadas' => $coresRelacionadas,
            'colorSiblings'     => $coresRelacionadas,
            'similares'         => $similares,
            'mostViewed'        => $mostViewed,
            'settings'          => $settings,
            'attribute'         => $attribute,
        ]);
    }

    private function getSimilares(Product $product)
    {
        return Cache::remember("pdp_similares_{$product->id}_v11", now()->addMinutes(10), function () use ($product) {
            $limit       = 8;
            $palabraClave = $this->palabraClaveSimilar($product);

            $base = $this->activeBase()
                ->where('id', '!=', $product->id)
                ->with('brand');

            $niveles = [];
            if ($palabraClave) {
                $niveles[] = ['childcategory_id', $product->childcategory_id, $palabraClave];
                $niveles[] = ['subcategory_id',   $product->subcategory_id,   $palabraClave];
                $niveles[] = ['category_id',      $product->category_id,      $palabraClave];
            }
            $niveles[] = ['childcategory_id', $product->childcategory_id, null];
            $niveles[] = ['subcategory_id',   $product->subcategory_id,   null];
            $niveles[] = ['category_id',      $product->category_id,      null];

            $similares = collect();
            foreach ($niveles as [$campo, $id, $palavra]) {
                $faltan = $limit - $similares->count();
                if ($faltan <= 0) break;
                if (!$id) continue;

                $similares = $similares->merge(
                    (clone $base)
                        ->where($campo, $id)
                        ->when($palavra, fn($q) => $q->where('external_name', 'LIKE', "%{$palavra}%"))
                        ->whereNotIn('id', $similares->pluck('id'))
                        ->inRandomOrder()
                        ->take($faltan)
                        ->get()
                );
            }

            return $similares->take($limit);
        });
    }

    private function palabraClaveSimilar(Product $product): ?string
    {
        $name = mb_strtolower($product->external_name ?? $product->name ?? '');
        foreach (array_keys($this->palabrasClaveProductos()) as $palavra) {
            if (str_contains($name, mb_strtolower($palavra))) {
                return $palavra;
            }
        }
        return null;
    }

    private function palabrasClaveProductos(): array
    {
        $path = public_path('data/product_keywords.json');
        if (!is_file($path)) return [];

        return Cache::remember('product_keywords_' . filemtime($path), now()->addHour(),
            fn() => (array) json_decode(file_get_contents($path), true)
        );
    }

    private function calcularPrecoComCupon(Product $p): float
    {
        $cupons = $p->cupons ?? collect();
        if ($cupons->isEmpty()) return $p->price;

        $desconto = $cupons->max(fn($c) => $c->tipo === 'percentual'
            ? $p->price * ($c->montante / 100)
            : $c->montante
        );

        return $desconto ? max(0, $p->price - $desconto) : $p->price;
    }

    private function sizeWeight(?string $size): float
    {
        if ($size === null || $size === '') return PHP_INT_MAX;

        $normalized = strtoupper(trim($size));

        if (preg_match('/^(\d+)\s*M$/', $normalized, $m)) {
            return (float) $m[1] + 0.5;
        }

        if (is_numeric($normalized)) return (float) $normalized;

        return [
            'PP' => 1, 'XS' => 1,
            'P'  => 2, 'S'  => 2,
            'M'  => 3,
            'G'  => 4, 'L'  => 4,
            'GG' => 5, 'XL' => 5,
            'XGG'=> 6, 'XXL'=> 6,
            'XXG'=> 7, 'XXXL'=> 7,
        ][$normalized] ?? PHP_INT_MAX - 1;
    }
}
