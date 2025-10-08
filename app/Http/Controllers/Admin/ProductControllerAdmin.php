<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
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
            ->when($stockFilter, fn($q) =>
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

        $brands = Brand::all();
        $categories = Category::all();

        $highlights = [
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
        $childcategories = collect();
        $products = Product::all(); // adiciona aqui também

        return view('admin.products.create', compact('brands', 'categories', 'subcategories', 'childcategories', 'products'));
    }

    // ProductControllerAdmin.php
    public function search(Request $request)
    {
        $q = $request->get('q');
        $products = Product::where('name', 'like', "%{$q}%")
            ->orWhere('external_name', 'like', "%{$q}%")
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
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
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
        $item = Product::findOrFail($id);
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::where('category_id', $item->category_id)->get();
        $childcategories = Childcategory::where('subcategory_id', $item->subcategory_id)->get();
        $products = Product::all(); // pega todos os produtos para seleção de parent/cores

        // Transformar parent_id e color_parent_id em arrays para o Blade
        $item->parent_id = is_string($item->parent_id) ? explode(',', $item->parent_id) : ($item->parent_id ?? []);
        $item->color_parent_id = is_string($item->color_parent_id) ? explode(',', $item->color_parent_id) : ($item->color_parent_id ?? []);

        return view('admin.products.edit', compact(
            'item',
            'brands',
            'categories',
            'subcategories',
            'childcategories',
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
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|array',
            'parent_id.*' => 'nullable|exists:products,id',
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        // Dados básicos
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
            'size',
            'color',
        ]);

        // Filtra arrays para não vir [""] e bagunçar
        $parentIds = array_filter((array) $request->input('parent_id', []));
        $colorIds  = array_filter((array) $request->input('color_parent_id', []));
        $data['highlights'] = json_encode($request->input('highlights', []));

        // Guarda filhos atuais antes da atualização
        $oldParentIds = $product->parent_id ? explode(',', $product->parent_id) : [];

        // Foto principal
        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                $usedElsewhere = Product::where('photo', $product->photo)
                    ->where('id', '!=', $product->id)
                    ->exists();
                if (!$usedElsewhere) {
                    Storage::disk('public')->delete($product->photo);
                }
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        // Cor
        if ($request->has('colors_values')) {
            $data['color'] = $request->input('colors_values')[0];
        }

        // Galeria
        if ($request->hasFile('gallery')) {
            $existingGallery = $product->gallery ? json_decode($product->gallery, true) : [];
            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $this->convertToWebp($image, 'gallery');
            }
            $data['gallery'] = json_encode($existingGallery);
        }

        // Define papel do produto automaticamente
        if (count($parentIds) === 0) {
            // Sem parent_id => é Pai
            $data['product_role'] = 'P';
            $data['parent_id'] = null;
            $data['color_parent_id'] = null;
        } else {
            // Tem parent_id => é Filho
            $data['product_role'] = 'F';
            $data['parent_id'] = implode(',', $parentIds);
            $data['color_parent_id'] = implode(',', $colorIds);
        }

        $product->update($data);

        // --- Remove filhos que não estão mais selecionados
        $removedChildren = array_diff($oldParentIds, $parentIds);
        foreach ($removedChildren as $childId) {
            $child = Product::find($childId);
            if ($child) {
                $child->parent_id = null;
                $child->product_role = 'P'; // volta a ser pai se não tiver outro
                $child->save();
            }
        }

        // --- Atualiza filhos
        $parentPhoto   = $product->photo;
        $parentGallery = $product->gallery ? json_decode($product->gallery, true) : [];

        foreach ($parentIds as $childId) {
            $child = Product::find($childId);
            if (!$child) continue;

            $child->parent_id = $product->id;
            $child->color_parent_id = implode(',', $colorIds);
            $child->product_role = 'F';

            // Copia foto do pai
            if ($parentPhoto && Storage::disk('public')->exists($parentPhoto)) {
                $newPhotoPath = 'products/photo/' . uniqid() . '.webp';
                Storage::disk('public')->copy($parentPhoto, $newPhotoPath);

                if ($child->photo && Storage::disk('public')->exists($child->photo)) {
                    $usedElsewhere = Product::where(function ($q) use ($child) {
                        $q->where('photo', $child->photo)
                            ->orWhere('gallery', 'like', "%{$child->photo}%");
                    })
                        ->where('id', '!=', $child->id)
                        ->exists();
                    if (!$usedElsewhere) {
                        Storage::disk('public')->delete($child->photo);
                    }
                }

                $child->photo = $newPhotoPath;
            }

            // Copia galeria do pai
            if (!empty($parentGallery)) {
                $newGallery = [];
                foreach ($parentGallery as $pgImg) {
                    if (Storage::disk('public')->exists($pgImg)) {
                        $newPath = 'products/gallery/' . uniqid() . '.webp';
                        Storage::disk('public')->copy($pgImg, $newPath);
                        $newGallery[] = $newPath;
                    }
                }

                $existingGallery = $child->gallery ? json_decode($child->gallery, true) : [];
                foreach ($existingGallery as $eg) {
                    if (Storage::disk('public')->exists($eg)) {
                        $usedElsewhere = Product::where('gallery', 'like', "%{$eg}%")
                            ->where('id', '!=', $child->id)
                            ->exists();
                        if (!$usedElsewhere) {
                            Storage::disk('public')->delete($eg);
                        }
                    }
                }

                $child->gallery = json_encode($newGallery);
            }

            $child->save();
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto atualizado com sucesso!');
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
