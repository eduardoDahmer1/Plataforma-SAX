<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = 40;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Query uploads
        $uploadsQuery = Upload::select([
            'id',
            'title',
            'description',
            'created_at',
            DB::raw("'upload' as type"),
            DB::raw("NULL as price")
        ]);

        // Query products
        $productsQuery = Product::select([
            'id',
            DB::raw("external_name as title"),
            DB::raw("sku as description"),
            'created_at',
            DB::raw("'product' as type"),
            'price'
        ]);

        // Aplica filtro de busca se houver
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

        // Monta SQL para union
        $uploadsSql = $uploadsQuery->toSql();
        $productsSql = $productsQuery->toSql();

        // Junta os bindings das queries
        $bindings = array_merge($uploadsQuery->getBindings(), $productsQuery->getBindings());

        // Calcula offset para paginação
        $offset = ($currentPage - 1) * $perPage;

        // SQL completo com union e paginação
        $unionSql = "({$uploadsSql}) UNION ALL ({$productsSql}) ORDER BY created_at DESC LIMIT ? OFFSET ?";

        // Acrescenta bindings para limit e offset
        $bindings[] = $perPage;
        $bindings[] = $offset;

        // Executa query
        $items = collect(DB::select($unionSql, $bindings));

        // Conta total para paginação
        $uploadsCount = $uploadsQuery->count();
        $productsCount = $productsQuery->count();
        $total = $uploadsCount + $productsCount;

        // Cria o paginator
        $paginated = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Aqui está o ajuste:
        return view('home', ['items' => $paginated]);
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
