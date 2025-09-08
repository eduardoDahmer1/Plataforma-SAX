<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $stockFilter = $request->get('stock_filter'); // 👈 novo filtro de estoque

        $productColumns = [
            'id',
            'sku',
            'external_name',
            'slug',
            'price',
            'stock',
            'photo',
            'brand_id',
            'category_id',
            'subcategory_id',
            'childcategory_id',
            'status',
            'highlights'
        ];

        $products = Product::select($productColumns)
            ->when(
                $search,
                fn($q) => $q
                    ->where('external_name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
            )
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($statusFilter, function($q) use ($statusFilter) {
                switch($statusFilter) {
                    case 'active':
                        $q->where('status', 1);
                        break;
                    case 'inactive':
                        $q->where('status', 0);
                        break;
            
                    // Filtros de imagem
                    case 'without_image':
                        $q->where(function($q2) {
                            $q2->where(function($q3){
                                $q3->whereNull('photo')
                                   ->orWhere('photo',''); // cobre string vazia
                            })
                            ->where(function($q4){
                                $q4->whereNull('gallery')
                                   ->orWhere('gallery','')
                                   ->orWhere('gallery','[]'); // cobre galeria vazia
                            });
                        });
                        break;
                    
                    case 'with_image':
                        $q->where(function($q2){
                            $q2->whereNotNull('photo')
                               ->where('photo','<>','')
                               ->orWhere(function($q3){
                                   $q3->whereNotNull('gallery')
                                      ->where('gallery','<>','')
                                      ->where('gallery','<>','[]');
                               });
                        });
                        break;
            
                    // Filtros de estoque
                    case 'out_of_stock':
                        $q->where('stock', 0);
                        break;
                    case 'in_stock':
                        $q->where('stock', '>', 0);
                        break;
                    case 'out_of_stock_and_active':
                        $q->where('stock', 0)->where('status', 1);
                        break;
                    case 'stock_and_inactive':
                        $q->where('stock', '>', 0)->where('status', 0);
                        break;
                }
            })
            
            
            ->when($highlightFilter, function ($q) use ($highlightFilter) {
                $q->whereJsonContains('highlights', [$highlightFilter => "1"]);
            })
            ->when($stockFilter, function ($q) use ($stockFilter) {
                if ($stockFilter === 'in_stock') {
                    $q->where('stock', '>', 0);
                } elseif ($stockFilter === 'out_of_stock') {
                    $q->where('stock', 0);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->query());

        $brands = Brand::all();
        $categories = Category::all();

        $highlights = [
            'destaque' => 'Exibir em Destaques',
            'mais_vendidos' => 'Exibir em Mais Vendidos',
            'melhores_avaliacoes' => 'Exibir em Melhores Avaliações',
            'super_desconto' => 'Exibir em Super Desconto',
            'famosos' => 'Exibir em Famosos',
            'lancamentos' => 'Exibir em Lançamentos',
            'tendencias' => 'Exibir em Tendências',
            'promocoes' => 'Exibir em Promoções',
            'ofertas_relampago' => 'Exibir em Ofertas Relâmpago',
            'navbar' => 'Exibir em Navbar',
        ];

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
            'highlights'
        ));
    }

    // ================== CREATE ==================
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = collect();
        $childcategories = collect();

        return view('admin.products.create', compact('brands', 'categories', 'subcategories', 'childcategories'));
    }

    // ================== STORE ==================
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array'
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
            'childcategory_id'
        ]);

        $data['highlights'] = $request->input('highlights', []);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
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
        $item = Product::findOrFail($id);
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::where('category_id', $item->category_id)->get();
        $childcategories = Childcategory::where('subcategory_id', $item->subcategory_id)->get();

        return view('admin.products.edit', compact('item', 'brands', 'categories', 'subcategories', 'childcategories'));
    }

    // ================== UPDATE ==================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array'
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
            'childcategory_id'
        ]);

        $data['highlights'] = $request->input('highlights', []);

        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('gallery')) {
            $existingGallery = $product->gallery ? json_decode($product->gallery, true) : [];
            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $this->convertToWebp($image, 'gallery');
            }
            $data['gallery'] = json_encode($existingGallery);
        }

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // ================== DELETE GALLERY IMAGE ==================
    public function deleteGalleryImage(Request $request, $productId, $imageName)
    {
        $product = Product::findOrFail($productId);
        if (!$product->gallery) {
            return redirect()->back()->with('error', 'Produto não possui galeria.');
        }

        $gallery = json_decode($product->gallery, true);
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

        $product->gallery = json_encode(array_values($gallery));
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
        return Childcategory::where('subcategory_id', $subcategoryId)->get();
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
}