<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductControllerAdmin extends Controller
{

    // ================== INDEX ==================
    public function index(Request $request)
    {
        $search = $request->get('search');
        $brandId = $request->get('brand_id');
        $categoryId = $request->get('category_id');
        $statusFilter = $request->get('status_filter');
        $highlightFilter = $request->get('highlight_filter');
        $stockFilter = $request->get('stock_filter');
        $sortBy = $request->get('sort_by');

        $productColumns = [
            'id',
            'sku',
            'name',
            'external_name',
            'slug',
            'price',
            'stock',
            'photo',
            'gallery',
            'brand_id',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            'status',
            'highlights'
        ];

        $products = Product::select($productColumns)
            ->when($search, fn($q) => $q
                ->where('external_name', 'LIKE', "%{$search}%")
                ->orWhere('sku', 'LIKE', "%{$search}%")
                ->orWhere('slug', 'LIKE', "%{$search}%"))
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($statusFilter, function ($q) use ($statusFilter) {
                switch ($statusFilter) {
                    case 'active':
                        $q->where('status', 1);
                        break;
                    case 'inactive':
                        $q->where('status', 0);
                        break;
                    case 'without_image':
                        $q->where(function ($q2) {
                            $q2->whereNull('photo')->orWhere('photo', '');
                        });
                        break;
                    case 'with_image':
                        $q->whereNotNull('photo');
                        break;
                    case 'out_of_stock':
                        $q->where('stock', 0);
                        break;
                    case 'in_stock':
                        $q->where('stock', '>', 0);
                        break;
                }
            })
            ->when($highlightFilter, fn($q) => $q->whereJsonContains('highlights', [$highlightFilter => "1"]))
            ->when(
                $stockFilter,
                fn($q) =>
                $stockFilter === 'in_stock'
                    ? $q->where('stock', '>', 0)
                    : ($stockFilter === 'out_of_stock' ? $q->where('stock', 0) : null)
            )
            ->when($sortBy, function ($q) use ($sortBy) {
                switch ($sortBy) {
                    case 'latest':
                        $q->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $q->orderBy('created_at', 'asc');
                        break;
                    case 'recently_updated':
                        $q->orderBy('updated_at', 'desc');
                        break;
                    case 'old_updated':
                        $q->orderBy('updated_at', 'asc');
                        break;
                    case 'price_low':
                        $q->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $q->orderBy('price', 'desc');
                        break;
                    case 'name_az':
                        $q->orderBy('external_name', 'asc');
                        break;
                    case 'name_za':
                        $q->orderBy('external_name', 'desc');
                        break;
                    default:
                        $q->orderBy('id', 'desc');
                        break;
                }
            }, function ($q) {
                $q->orderBy('id', 'desc');
            })
            ->paginate(20)
            ->appends($request->query());

        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();

        $highlights = [
            'destaque' => 'Destaques',
            'lancamentos' => 'Lançamentos',
        ];

        // Ajuste das URLs das imagens
        $products->getCollection()->transform(function ($product) {
            $product->imageUrl = $product->photo_url;
            return $product;
        });

        return view('admin.products.index', compact(
            'products',
            'brands',
            'categories',
            'search',
            'brandId',
            'categoryId',
            'statusFilter',
            'highlightFilter',
            'stockFilter',
            'sortBy',
            'highlights'
        ));
    }

    // ================== CREATE ==================
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = collect();
        $categoriasfilhas = collect();
        $products = Product::all(); // adiciona aqui também

        return view('admin.products.create', compact('brands', 'categories', 'subcategories', 'categorias-filhas', 'products'));
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $products = Product::where('status', 1) // Garante que o produto esteja ativo
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('external_name', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'external_name']);

        return response()->json($products);
    }

    // ================== STORE ==================
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:categorias-filhas,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|exists:products,id',
            'color' => 'nullable|string|max:7',
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:50'
        ]);

        $data = $request->only([
            'sku',
            'external_name',
            'description',
            'price',
            'stock',
            'brand_id',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            'parent_id',
            'color',
            'size'
        ]);

        $data['highlights'] = $request->input('highlights', []);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }


        // Salva a cor selecionada
        if ($request->has('colors_values')) {
            $data['color'] = $request->input('colors_values')[0];
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $this->convertToWebp($image, 'gallery');
            }
            $data['gallery'] = json_encode($galleryPaths);
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Produto criado com sucesso!');
    }

    // ================== EDIT ==================
    public function edit($id)
    {
        $item = Product::with([
            'brand',
            'category',
            'subcategory',
            'categoriasfilhas'
        ])->findOrFail($id);

        // Carrega apenas categorias e subcategorias necessárias
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::where('category_id', $item->category_id)->get();
        $categoriasfilhas = CategoriasFilhas::where('subcategory_id', $item->subcategory_id)->get();

        // Carrega somente os produtos que podem ser selecionados como parent ou cores
        $products = Product::select('id', 'name')->get(); // << name adicionado aqui

        // Transformar parent_id e color_parent_id em arrays
        $item->parent_id = is_string($item->parent_id) ? explode(',', $item->parent_id) : ($item->parent_id ?? []);
        $item->color_parent_id = is_string($item->color_parent_id) ? explode(',', $item->color_parent_id) : ($item->color_parent_id ?? []);

        return view('admin.products.edit', compact(
            'item',
            'brands',
            'categories',
            'subcategories',
            'categoriasfilhas',
            'products'
        ));
    }
    // ================== UPDATE ==================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validação
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:categoriasfilhas,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|array', // IDs dos produtos selecionados para serem FILHOS
            'parent_id.*' => 'nullable|exists:products,id',
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:50',
            'stores' => 'nullable|array',
            'stores.*' => 'string|in:asuncion,cde,pjc', // Valida se os valores são os permitidos
            'color' => 'nullable|string|max:7',
        ]);

        $data = $request->only([
            'sku',
            'name',
            'description',
            'price',
            'stock',
            'brand_id',
            'stores',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            'size',
            'color',
        ]);

        // IDs selecionados no formulário para serem filhos deste produto
        $selectedChildrenIds = array_filter((array) $request->input('parent_id', []));

        // Highlights como JSON
        $data['highlights'] = json_encode($request->input('highlights', []));

        // O produto sendo editado agora é garantido como PAI (P)
        // A menos que você queira que ele mesmo seja filho de outro, 
        // mas conforme sua regra, estamos definindo a estrutura a partir do pai.
        $data['product_role'] = 'P';
        $data['parent_id'] = null;
        $data['stores'] = $request->input('stores', []);

        // --- TRATAMENTO DE IMAGEM PRINCIPAL ---
        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                $usedElsewhere = Product::where('photo', $product->photo)->where('id', '!=', $product->id)->exists();
                if (!$usedElsewhere) {
                    Storage::disk('public')->delete($product->photo);
                }
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        // --- TRATAMENTO DE GALERIA ---
        if ($request->hasFile('gallery')) {
            $existingGallery = $product->gallery;
            if (!is_array($existingGallery)) {
                $existingGallery = $existingGallery ? json_decode($existingGallery, true) : [];
            }

            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $this->convertToWebp($image, 'gallery');
            }
            $data['gallery'] = json_encode($existingGallery);
        }

        // Pega os IDs dos filhos que este pai já tinha antes da atualização
        $oldChildrenIds = Product::where('parent_id', $product->id)->pluck('id')->toArray();

        // Atualiza o produto Pai
        $product->update($data);

        // --- LÓGICA PARA OS PRODUTOS FILHOS SELECIONADOS ---

        // 1. Desvincular quem foi removido da lista
        $removedChildren = array_diff($oldChildrenIds, $selectedChildrenIds);
        foreach ($removedChildren as $childId) {
            $child = Product::find($childId);
            if ($child) {
                $child->update([
                    'parent_id' => null,
                    'product_role' => 'P', // Volta a ser pai/independente
                    'color_parent_id' => null
                ]);
            }
        }

        // 2. Atualizar/Vincular novos filhos
        foreach ($selectedChildrenIds as $childId) {
            $child = Product::find($childId);
            if ($child && $child->id != $product->id) { // Evita auto-referência
                $child->update([
                    'parent_id' => $product->id,    // ID do pai que estamos editando
                    'product_role' => 'F',          // Define como filho
                    'description' => $product->description,
                    'photo' => $product->photo,
                    'gallery' => is_array($product->gallery) ? json_encode($product->gallery) : $product->gallery,
                    'brand_id' => $product->brand_id,
                    'category_id' => $product->category_id,
                    'subcategory_id' => $product->subcategory_id,
                    'status' => $product->status,
                    'stores' => $product->stores
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produto Pai e seus filhos foram atualizados com sucesso!');
    }

    /**
     * Função auxiliar para deletar imagens de um produto sem afetar outros
     */
    private function deleteProductImages($product)
    {
        // Foto principal
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            $usedElsewhere = Product::where('photo', $product->photo)->where('id', '!=', $product->id)->exists();
            if (!$usedElsewhere) {
                Storage::disk('public')->delete($product->photo);
            }
        }

        // Galeria
        if ($product->gallery) {
            $gallery = is_string($product->gallery) ? json_decode($product->gallery, true) : (array) $product->gallery;
            foreach ($gallery as $img) {
                if (Storage::disk('public')->exists($img)) {
                    $usedElsewhere = Product::where('gallery', 'like', "%{$img}%")->where('id', '!=', $product->id)->exists();
                    if (!$usedElsewhere) {
                        Storage::disk('public')->delete($img);
                    }
                }
            }
        }
    }

    // ================== DELETE GALLERY IMAGE ==================
    public function deleteGalleryImage(Request $request, $productId, $imageName)
    {
        $product = Product::findOrFail($productId);

        if (!$product->gallery) {
            return redirect()->back()->with('error', 'Produto não possui galeria.');
        }

        // garante que seja array
        $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);

        $imagePath = null;

        foreach ($gallery as $key => $img) {
            if (basename($img) === $imageName) {
                $imagePath = $img;
                unset($gallery[$key]);
                break;
            }
        }

        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $product->gallery = $gallery; // já salva como array
        $product->save();

        return redirect()->back()->with('success', 'Imagem da galeria removida com sucesso!');
    }

    // ================== DELETE MAIN PHOTO ==================
    public function deletePhoto($productId)
    {
        $product = Product::findOrFail($productId);
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
            $product->photo = null;
            $product->save();
            return redirect()->back()->with('success', 'Foto principal removida com sucesso!');
        }
        return redirect()->back()->with('error', 'Produto não possui foto principal.');
    }

    // ================== DESTROY ==================
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            if ($product->gallery) {
                foreach (json_decode($product->gallery, true) as $img) {
                    if (Storage::disk('public')->exists($img)) Storage::disk('public')->delete($img);
                }
            }
            $product->delete();
        }
        return redirect()->route('admin.products.index')->with('success', 'Produto excluído com sucesso!');
    }

    // ================== AUX ==================
    private function convertToWebp($image, $type)
    {
        $temp = $image->getRealPath();
        $ext = strtolower($image->getClientOriginalExtension());

        $imgRes = match ($ext) {
            'jpeg', 'jpg' => imagecreatefromjpeg($temp),
            'png' => imagecreatefrompng($temp),
            'gif' => imagecreatefromgif($temp),
            'webp' => imagecreatefromwebp($temp),
            default => throw new \Exception('Formato de imagem não suportado')
        };

        ob_start();
        imagewebp($imgRes, null, 90);
        $webpData = ob_get_clean();
        imagedestroy($imgRes);

        $dir = "products/{$type}/";
        $fileName = uniqid() . '.webp';
        if (!Storage::disk('public')->exists($dir)) Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put("{$dir}{$fileName}", $webpData);

        return "{$dir}{$fileName}";
    }

    public function getSubcategories($categoryId)
    {
        return Subcategory::where('category_id', $categoryId)->get();
    }

    public function getChildcategories($subcategoryId)
    {
        return CategoriasFilhas::where('subcategory_id', $subcategoryId)->get();
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = !$product->status;
        $product->save();

        return redirect()->back()->with('success', 'Status do produto atualizado!');
    }

    public function updateHighlights(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $highlights = $request->input('highlights', []);
        $product->highlights = json_encode($highlights);
        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Destaques atualizados com sucesso!');
    }

    public function review()
    {
        $edicoesPorDia = Product::selectRaw('DATE(updated_at) as dia, COUNT(*) as total')
            ->whereNotNull('updated_at')
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get();

        return view('admin.products.review', compact('edicoesPorDia'));
    }
}
