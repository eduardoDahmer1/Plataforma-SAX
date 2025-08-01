<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UploadController extends Controller
{
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
            DB::raw('NULL as price'),
            'file_path',
            'original_name'
        ]);

        $productsQuery = Product::select([
            'id',
            DB::raw("external_name as title"),
            DB::raw("sku as description"),
            'created_at',
            DB::raw("'product' as type"),
            'price',
            DB::raw('NULL as file_path'),
            DB::raw('NULL as original_name')
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

        return view('admin.uploads.index', ['uploads' => $paginated]);
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $path = $image->store('uploads', 'public');

            return response()->json([
                'success' => true,
                'file' => Storage::url($path)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Nenhuma imagem enviada.']);
    }

    public function allUploads()
    {
        $uploads = Upload::with('user')->get();
        return view('admin.uploads.index', compact('uploads'));
    }

    public function create()
    {
        return view('admin.uploads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'title' => 'required|string|max:10240',
            'description' => 'nullable|string|max:10000',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        Upload::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_type' => $file->getClientOriginalExtension(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'user_id' => auth()->id() ?? null,
        ]);

        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo enviado com sucesso!');
    }

    public function edit(string $id)
    {
        $upload = Upload::findOrFail($id);
        return view('admin.uploads.edit', compact('upload'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Apenas atualiza a descrição
        $product->description = $request->input('description');
        $product->save();

        return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $upload = Upload::findOrFail($id);

        // Apaga imagens da descrição (se houver)
        $images = $this->extractImageUrls($upload->description);
        foreach ($images as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $relative = ltrim(str_replace('/storage/', '', $path), '/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        }

        if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        $upload->delete();

        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo excluído com sucesso!');
    }

    public function deleteImages($id)
    {
        $product = Product::findOrFail($id);

        // Remove imagens do storage contidas na descrição
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($product->description, 'HTML-ENTITIES', 'UTF-8'));

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            if (str_contains($src, '/storage/')) {
                $path = str_replace('/storage/', '', parse_url($src, PHP_URL_PATH));
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        // Limpa a descrição
        $product->description = null;
        $product->save();

        return back()->with('success', 'Imagens e descrição removidas com sucesso.');
    }

    private function extractImageUrls($html)
    {
        $urls = [];

        if (!$html) {
            return $urls;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        foreach ($dom->getElementsByTagName('img') as $img) {
            $src = $img->getAttribute('src');
            if ($src) {
                $urls[] = $src;
            }
        }
        return $urls;
    }

    public function show($id)
    {
        $upload = Upload::findOrFail($id);
        return view('admin.uploads.show', compact('upload'));
    }
}
