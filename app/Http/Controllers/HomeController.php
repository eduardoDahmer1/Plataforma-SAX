<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    // REMOVE o middleware 'auth' pra home ficar pública
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = 40;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $uploadsQuery = Upload::select([
            'id',
            'title',
            'description',
            'created_at',
            DB::raw("'upload' as type"),
            DB::raw("NULL as price")
        ]);

        $productsQuery = Product::select([
            'id',
            DB::raw("external_name as title"),
            DB::raw("sku as description"),
            'created_at',
            DB::raw("'product' as type"),
            'price'
        ]);

        if ($search) {
            $uploadsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            $productsQuery->where(function ($q) use ($search) {
                $q->where('external_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $uploadsSql = $uploadsQuery->toSql();
        $productsSql = $productsQuery->toSql();
        $bindings = array_merge($uploadsQuery->getBindings(), $productsQuery->getBindings());

        $offset = ($currentPage - 1) * $perPage;

        $unionSql = "({$uploadsSql}) UNION ALL ({$productsSql}) ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $bindings[] = $perPage;
        $bindings[] = $offset;

        $items = collect(DB::select($unionSql, $bindings));

        $uploadsCount = $uploadsQuery->count();
        $productsCount = $productsQuery->count();
        $total = $uploadsCount + $productsCount;

        $paginated = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('home', ['items' => $paginated]);
    }

    // Métodos vazios caso queira implementar depois
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
