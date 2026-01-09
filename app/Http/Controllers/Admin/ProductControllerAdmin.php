<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Services\ImageService;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductControllerAdmin extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    // ================== INDEX ==================
    public function index(Request $request)
    {
        $products = $this->buildProductQuery($request)
            ->paginate(20)
            ->withQueryString();

        $products->getCollection()->transform(fn($product) => 
            $product->setAttribute('imageUrl', $product->photo_url)
        );

        return view('admin.products.index', array_merge(
            compact('products'),
            $this->getViewData($request)
        ));
    }

    // ================== CREATE ==================
    public function create()
    {
        return view('admin.products.create', [
            'brands' => Brand::all(),
            'categories' => Category::all(),
            'subcategories' => collect(),
            'childcategories' => collect(),
            'products' => Product::select('id', 'name', 'external_name')->get()
        ]);
    }

    // ================== SEARCH ==================
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('external_name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'external_name']);

        return response()->json($products);
    }

    // ================== STORE ==================
    public function store(ProductStoreRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $this->prepareProductData($request);
            
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->convertToWebp($request->file('photo'), 'photo');
            }

            if ($request->hasFile('gallery')) {
                $data['gallery'] = $this->processGalleryImages($request->file('gallery'));
            }

            Product::create($data);
            
            DB::commit();
            
            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto criado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    // ================== EDIT ==================
    public function edit($id)
    {
        $item = Product::with(['brand', 'category', 'subcategory', 'childcategory'])
            ->findOrFail($id);

        $item->parent_id = $this->parseToArray($item->parent_id);
        $item->color_parent_id = $this->parseToArray($item->color_parent_id);

        return view('admin.products.edit', [
            'item' => $item,
            'brands' => Brand::all(),
            'categories' => Category::all(),
            'subcategories' => Subcategory::where('category_id', $item->category_id)->get(),
            'childcategories' => Childcategory::where('subcategory_id', $item->subcategory_id)->get(),
            'products' => Product::select('id', 'name', 'external_name')->get()
        ]);
    }

    // ================== UPDATE ==================
    public function update(ProductUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $product = Product::findOrFail($id);
            $oldParentIds = $this->parseToArray($product->parent_id);
            
            $data = $this->prepareProductData($request, $product);
            
            // Processa imagens
            $this->handleImageUpdates($request, $product, $data);
            
            // Define role do produto
            $parentIds = array_filter((array) $request->input('parent_id', []));
            $this->setProductRole($data, $parentIds, $request);
            
            $product->update($data);
            
            // Sincroniza relacionamentos pai-filho
            $this->syncParentChildRelationships($product, $oldParentIds, $parentIds);
            
            DB::commit();
            
            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto atualizado com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    // ================== DELETE GALLERY IMAGE ==================
    public function deleteGalleryImage(Request $request, $productId, $imageName)
    {
        try {
            $product = Product::findOrFail($productId);
            
            if (!$product->gallery) {
                return redirect()->back()->with('error', 'Produto não possui galeria.');
            }

            $gallery = is_array($product->gallery) 
                ? $product->gallery 
                : json_decode($product->gallery, true);

            $imagePath = null;
            
            foreach ($gallery as $key => $img) {
                if (basename($img) === $imageName) {
                    $imagePath = $img;
                    unset($gallery[$key]);
                    break;
                }
            }

            if ($imagePath) {
                $this->imageService->deleteIfUnused($imagePath, $product->id, 'gallery');
            }

            $product->gallery = array_values($gallery);
            $product->save();

            return redirect()->back()->with('success', 'Imagem removida com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover imagem.');
        }
    }

    // ================== DELETE MAIN PHOTO ==================
    public function deletePhoto($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            if (!$product->photo) {
                return redirect()->back()->with('error', 'Produto não possui foto principal.');
            }

            $this->imageService->deleteIfUnused($product->photo, $product->id, 'photo');
            
            $product->photo = null;
            $product->save();
            
            return redirect()->back()->with('success', 'Foto principal removida com sucesso!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover foto.');
        }
    }

    // ================== DESTROY ==================
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $product = Product::findOrFail($id);
            
            // Remove imagens
            if ($product->photo) {
                $this->imageService->deleteIfUnused($product->photo, $product->id, 'photo');
            }
            
            if ($product->gallery) {
                $gallery = is_array($product->gallery) 
                    ? $product->gallery 
                    : json_decode($product->gallery, true);
                    
                foreach ($gallery as $img) {
                    $this->imageService->deleteIfUnused($img, $product->id, 'gallery');
                }
            }
            
            $product->delete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto excluído com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir produto.');
        }
    }

    // ================== AJAX ENDPOINTS ==================
    public function getSubcategories($categoryId)
    {
        return response()->json(
            Subcategory::where('category_id', $categoryId)->get()
        );
    }

    public function getChildcategories($subcategoryId)
    {
        return response()->json(
            Childcategory::where('subcategory_id', $subcategoryId)->get()
        );
    }

    public function toggleStatus($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->status = !$product->status;
            $product->save();

            return redirect()->back()->with('success', 'Status atualizado!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar status.');
        }
    }

    public function updateHighlights(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->highlights = json_encode($request->input('highlights', []));
            $product->save();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Destaques atualizados!');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar destaques.');
        }
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

    // ================== PRIVATE HELPERS ==================
    private function buildProductQuery(Request $request)
    {
        $query = Product::select([
            'id', 'sku', 'name', 'external_name', 'slug', 'price', 
            'stock', 'photo', 'gallery', 'brand_id', 'category_id',
            'subcategory_id', 'childcategory_id', 'status', 'highlights'
        ]);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('external_name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        // Filters
        if ($brandId = $request->get('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($highlightFilter = $request->get('highlight_filter')) {
            $query->whereJsonContains('highlights', [$highlightFilter => "1"]);
        }

        // Status filters
        $this->applyStatusFilter($query, $request->get('status_filter'));
        $this->applyStockFilter($query, $request->get('stock_filter'));
        
        // Sorting
        $this->applySorting($query, $request->get('sort_by', 'id_desc'));

        return $query;
    }

    private function applyStatusFilter($query, $statusFilter)
    {
        if (!$statusFilter) return;

        match($statusFilter) {
            'active' => $query->where('status', 1),
            'inactive' => $query->where('status', 0),
            'without_image' => $query->where(fn($q) => $q->whereNull('photo')->orWhere('photo', '')),
            'with_image' => $query->whereNotNull('photo')->where('photo', '!=', ''),
            'out_of_stock' => $query->where('stock', 0),
            'in_stock' => $query->where('stock', '>', 0),
            default => null
        };
    }

    private function applyStockFilter($query, $stockFilter)
    {
        if (!$stockFilter) return;

        match($stockFilter) {
            'in_stock' => $query->where('stock', '>', 0),
            'out_of_stock' => $query->where('stock', 0),
            default => null
        };
    }

    private function applySorting($query, $sortBy)
    {
        match($sortBy) {
            'latest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'recently_updated' => $query->orderBy('updated_at', 'desc'),
            'old_updated' => $query->orderBy('updated_at', 'asc'),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name_az' => $query->orderBy('external_name', 'asc'),
            'name_za' => $query->orderBy('external_name', 'desc'),
            default => $query->orderBy('id', 'desc')
        };
    }

    private function getViewData(Request $request)
    {
        return [
            'brands' => Brand::all(),
            'categories' => Category::all(),
            'highlights' => $this->getHighlightsList(),
            'search' => $request->get('search'),
            'brandId' => $request->get('brand_id'),
            'categoryId' => $request->get('category_id'),
            'statusFilter' => $request->get('status_filter'),
            'highlightFilter' => $request->get('highlight_filter'),
            'stockFilter' => $request->get('stock_filter'),
            'sortBy' => $request->get('sort_by')
        ];
    }

    private function getHighlightsList()
    {
        return [
            'destaque' => 'Destaques',
            'mais_vendidos' => 'Mais Vendidos',
            'melhores_avaliacoes' => 'Melhores Avaliações',
            'super_desconto' => 'Super Desconto',
            'famosos' => 'Famosos',
            'lancamentos' => 'Lançamentos',
            'tendencias' => 'Tendências',
            'promocoes' => 'Promoções',
            'ofertas_relampago' => 'Ofertas Relâmpago',
            'navbar' => 'Navbar',
        ];
    }

    private function prepareProductData(Request $request, ?Product $product = null)
    {
        $data = $request->only([
            'sku', 'name', 'external_name', 'description', 'price', 
            'stock', 'brand_id', 'category_id', 'subcategory_id',
            'childcategory_id', 'size', 'color'
        ]);

        $data['highlights'] = json_encode($request->input('highlights', []));

        if ($request->has('colors_values')) {
            $data['color'] = $request->input('colors_values')[0];
        }

        return $data;
    }

    private function handleImageUpdates(Request $request, Product $product, &$data)
    {
        // Foto principal
        if ($request->hasFile('photo')) {
            $this->imageService->deleteIfUnused($product->photo, $product->id, 'photo');
            $data['photo'] = $this->imageService->convertToWebp($request->file('photo'), 'photo');
        }

        // Galeria
        if ($request->hasFile('gallery')) {
            $existingGallery = is_array($product->gallery) 
                ? $product->gallery 
                : ($product->gallery ? json_decode($product->gallery, true) : []);
                
            $newImages = $this->processGalleryImages($request->file('gallery'));
            $data['gallery'] = json_encode(array_merge($existingGallery, json_decode($newImages, true)));
        }
    }

    private function setProductRole(&$data, $parentIds, Request $request)
    {
        if (count($parentIds) === 0) {
            $data['product_role'] = 'P';
            $data['parent_id'] = null;
            $data['color_parent_id'] = null;
        } else {
            $data['product_role'] = 'F';
            $data['parent_id'] = implode(',', $parentIds);
            
            $colorIds = array_filter((array) $request->input('color_parent_id', []));
            $data['color_parent_id'] = implode(',', $colorIds);
        }
    }

    private function syncParentChildRelationships(Product $product, array $oldParentIds, array $newParentIds)
    {
        // Remove filhos que não estão mais selecionados
        $removedChildren = array_diff($oldParentIds, $newParentIds);
        foreach ($removedChildren as $childId) {
            $this->resetChildProduct($childId);
        }

        // Atualiza filhos selecionados
        foreach ($newParentIds as $childId) {
            $this->updateChildProduct($childId, $product, $newParentIds);
        }
    }

    private function resetChildProduct($childId)
    {
        $child = Product::find($childId);
        if (!$child) return;

        $this->imageService->deleteIfUnused($child->photo, $child->id, 'photo');
        
        if ($child->gallery) {
            $gallery = is_array($child->gallery) 
                ? $child->gallery 
                : json_decode($child->gallery, true);
                
            foreach ($gallery as $img) {
                $this->imageService->deleteIfUnused($img, $child->id, 'gallery');
            }
        }

        $child->update([
            'parent_id' => null,
            'product_role' => 'P',
            'photo' => null,
            'gallery' => null
        ]);
    }

    private function updateChildProduct($childId, Product $parent, array $allParentIds)
    {
        $child = Product::find($childId);
        if (!$child) return;

        $updateData = [
            'parent_id' => implode(',', $allParentIds),
            'product_role' => 'F'
        ];

        if ($parent->photo) {
            $this->imageService->deleteIfUnused($child->photo, $child->id, 'photo');
            $updateData['photo'] = $parent->photo;
        }

        $parentGallery = is_array($parent->gallery) 
            ? $parent->gallery 
            : json_decode($parent->gallery, true);
            
        if ($parentGallery) {
            if ($child->gallery) {
                $oldGallery = is_array($child->gallery) 
                    ? $child->gallery 
                    : json_decode($child->gallery, true);
                    
                foreach ($oldGallery as $img) {
                    $this->imageService->deleteIfUnused($img, $child->id, 'gallery');
                }
            }
            $updateData['gallery'] = json_encode($parentGallery);
        }

        if ($parent->color) {
            $updateData['color'] = $parent->color;
        }

        $child->update($updateData);
    }

    private function processGalleryImages($images)
    {
        $galleryPaths = [];
        foreach ($images as $image) {
            $galleryPaths[] = $this->imageService->convertToWebp($image, 'gallery');
        }
        return json_encode($galleryPaths);
    }

    private function parseToArray($value)
    {
        if (is_array($value)) return $value;
        if (is_string($value) && !empty($value)) return explode(',', $value);
        return [];
    }
}