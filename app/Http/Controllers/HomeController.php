<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Cart;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 🔹 Tipos de destaques
        $highlightTypes = [
            'destaque','mais_vendidos','melhores_avaliacoes','super_desconto',
            'famosos','lancamentos','tendencias','promocoes','ofertas_relampago','navbar'
        ];

        // 🔹 Busca produtos por destaque no JSON (objeto com chave => "1")
        $highlights = [];
        foreach($highlightTypes as $key){
            $highlights[$key] = Cache::remember("highlight_{$key}", 600, function() use($key){
                return Product::where("highlights->{$key}", "1")->get();
            });
        }

        // 🔹 Cache das brands
        $brandsKey = 'brands_' . md5(json_encode([
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'childcategory' => $request->childcategory,
        ]));

        $brands = Cache::remember($brandsKey, 600, function () use ($request) {
            return Brand::whereHas('products', fn($q)=>
                $q->when($request->category, fn($q2)=>$q2->where('category_id', $request->category))
                  ->when($request->subcategory, fn($q2)=>$q2->where('subcategory_id', $request->subcategory))
                  ->when($request->childcategory, fn($q2)=>$q2->where('childcategory_id', $request->childcategory))
            )->orderBy('name')->get();
        });

        // 🔹 Categorias
        $categories = Cache::remember('categories_all', 600, fn()=> 
            Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get()
        );

        $subcategories = Cache::remember('subcategories_all', 600, fn()=> 
            Subcategory::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get()
        );

        $childcategories = Cache::remember('childcategories_all', 600, fn()=> 
            Childcategory::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get()
        );

        // 🔹 Carrinho do usuário
        $cartItems = [];
        if($user = $request->user()){
            $cartItems = Cart::where('user_id',$user->id)
                ->pluck('quantity','product_id')
                ->toArray();
        }

        return view('home', [
            'brands' => $brands,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'childcategories' => $childcategories,
            'cartItems' => $cartItems,
            'highlights' => $highlights, // Produtos destacados
        ]);
    }
}
