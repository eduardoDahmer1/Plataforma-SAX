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

        $brands = Brand::where('status', 1)
            ->orWhere('id', $item->brand_id)
            ->orderBy('name', 'asc')
            ->get();

        $categories = Category::where('status', 1)
            ->orWhere('id', $item->category_id)
            ->orderBy('name', 'asc')
            ->get();

        $subcategories = $item->category_id
            ? Subcategory::where('category_id', $item->category_id)->get()
            : collect();

        $categoriasfilhas = $item->subcategory_id
            ? CategoriasFilhas::where('subcategory_id', $item->subcategory_id)->get()
            : collect();

        $products = Product::select('id', 'name', 'sku')
            ->where('id', '!=', $id)
            ->orderBy('name', 'asc')
            ->get();

        $item->selected_children = Product::where('parent_id', $item->id)
            ->pluck('id')
            ->toArray();

        $item->parent_id = is_string($item->parent_id) ? explode(',', $item->parent_id) : ($item->parent_id ?? []);

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

        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|array',
            'stores' => 'nullable|array',
            'color' => 'nullable|string|max:7',
        ]);

        try {
            $data = $request->only([
                'sku',
                'name',
                'description',
                'price',
                'stock',
                'brand_id',
                'category_id',
                'subcategory_id',
                'childcategory_id',
                'size',
                'color'
            ]);

            // --- IMAGEM PRINCIPAL ---
            if ($request->hasFile('photo')) {
                if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                    Storage::disk('public')->delete($product->photo);
                }
                $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
            }

            // --- GALERIA ---
            $currentGallery = is_string($product->gallery) ? json_decode($product->gallery, true) : ($product->gallery ?? []);
            if (!is_array($currentGallery)) $currentGallery = [];

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $newImagePath = $this->convertToWebp($image, 'gallery');
                    if ($newImagePath) $currentGallery[] = $newImagePath;
                }
            }
            $data['gallery'] = json_encode(array_values($currentGallery));

            // --- STATUS AUTOMÁTICO ---
            $hasPhoto = !empty($data['photo']) || !empty($product->photo);
            $data['status'] = ($hasPhoto && !empty($data['name']) && $data['price'] > 5 && $data['stock'] > 0) ? 1 : 0;

            // --- OUTROS DADOS ---
            $data['highlights'] = json_encode($request->input('highlights', []));
            $data['stores'] = $request->input('stores', []);
            $data['product_role'] = 'P';

            $product->update($data);

            // --- ATUALIZAR FILHOS ---
            $selectedChildrenIds = array_filter((array) $request->input('parent_id', []));

            // Remove parentesco de quem saiu da lista
            Product::where('parent_id', $product->id)
                ->whereNotIn('id', $selectedChildrenIds)
                ->update(['parent_id' => null, 'product_role' => 'P']);

            // Atualiza os filhos atuais
            if (!empty($selectedChildrenIds)) {
                Product::whereIn('id', $selectedChildrenIds)->update([
                    'parent_id'      => $product->id,
                    'product_role'   => 'F',
                    'description'    => $product->description,
                    'photo'          => $product->photo,
                    'gallery'        => $data['gallery'],
                    'brand_id'       => $product->brand_id,
                    'category_id'    => $product->category_id,
                    'status'         => $product->status,
                    'stores'         => json_encode($data['stores'])
                ]);
            }

            $returnTo = $request->input('return_to');

            // Redireciona de volta à página anterior (com filtros preservados) se a URL
            // pertencer ao próprio domínio. Evita open redirect para domínios externos.
            if ($returnTo && str_starts_with($returnTo, config('app.url'))) {
                return redirect($returnTo)->with('success', 'Produto atualizado com sucesso!');
            }

            // Fallback: redireciona ao índice sem filtros
            return redirect()->route('admin.products.index')->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            \Log::error("Erro no Update de Produto: " . $e->getMessage());
            return back()->with('error', 'Erro ao salvar: ' . $e->getMessage())->withInput();
        }
    }

    // ================== AUX ==================
    private function convertToWebp($image, $type)
    {
        try {
            $temp = $image->getRealPath();

            // imagecreatefromstring identifica automaticamente se é JPG, PNG ou WEBP
            $imgRes = @imagecreatefromstring(file_get_contents($temp));

            if (!$imgRes) {
                return null;
            }

            ob_start();
            imagewebp($imgRes, null, 85);
            $webpData = ob_get_clean();
            imagedestroy($imgRes);

            $dir = "products/{$type}/";
            $fileName = uniqid() . '.webp';

            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }

            Storage::disk('public')->put($dir . $fileName, $webpData);

            return $dir . $fileName;
        } catch (\Exception $e) {
            \Log::error("Erro na conversão WebP: " . $e->getMessage());
            return null;
        }
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

        // Garante que seja array
        $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);

        // Segurança caso o decode falhe
        if (!is_array($gallery)) {
            $gallery = [];
        }

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

        // IMPORTANTE: array_values reindexa [0, 1, 2] evitando chaves puladas no JSON
        $product->gallery = array_values($gallery);
        $product->save();

        return redirect()->back()->with('success', 'Imagem da galeria removida com sucesso!');
    }

    public function multiDeleteGalleryImage(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $imagesToDelete = explode(',', $request->image_names); // Recebe nomes separados por vírgula

        $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
        if (!is_array($gallery)) return redirect()->back();

        $newGallery = [];
        foreach ($gallery as $img) {
            if (in_array(basename($img), $imagesToDelete)) {
                if (Storage::disk('public')->exists($img)) {
                    Storage::disk('public')->delete($img);
                }
            } else {
                $newGallery[] = $img;
            }
        }

        $product->gallery = array_values($newGallery);
        $product->save();

        return redirect()->back()->with('success', count($imagesToDelete) . ' imagens removidas.');
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
        // Dados para os Cards (o que você já tinha)
        $edicoesPorDia = Product::selectRaw('DATE(updated_at) as dia, COUNT(*) as total')
            ->whereNotNull('updated_at')
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get();

        // Dados para os Modais (Busca os detalhes dos produtos editados nos últimos dias)
        // Limitamos a busca para não pesar o carregamento inicial
        $detalhesProdutos = Product::whereNotNull('updated_at')
            ->where('updated_at', '>=', now()->subDays(30)) // Pega os últimos 30 dias por segurança
            ->selectRaw('DATE(updated_at) as dia, name, sku, ref_code')
            ->get()
            ->groupBy('dia'); // Agrupa por data para o JS encontrar fácil

        return view('admin.products.review', compact('edicoesPorDia', 'detalhesProdutos'));
    }
}
