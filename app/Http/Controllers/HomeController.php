<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = 40;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
    
        // Monta as queries com os campos alinhados
        $uploadsQuery = Upload::select([
            'id',
            'title',
            'description',
            'created_at',
            DB::raw("'upload' as type"),
            DB::raw("NULL as price") // para alinhar colunas no union
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
    
        // Converte as queries para SQL e bindings
        $uploadsSql = $uploadsQuery->toSql();
        $productsSql = $productsQuery->toSql();
    
        $bindings = array_merge($uploadsQuery->getBindings(), $productsQuery->getBindings());
    
        // Monta o SQL union para paginação
        $unionSql = "({$uploadsSql}) UNION ALL ({$productsSql}) ORDER BY created_at DESC LIMIT ? OFFSET ?";
    
        // Calcula offset
        $offset = ($currentPage - 1) * $perPage;
    
        // Acrescenta limite e offset aos bindings
        $bindings[] = $perPage;
        $bindings[] = $offset;
    
        // Executa a query paginada
        $items = collect(DB::select($unionSql, $bindings));
    
        // Conta total para paginação (se a busca for simples, conta separadamente)
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
    
        return view('pages.home', ['items' => $paginated]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
