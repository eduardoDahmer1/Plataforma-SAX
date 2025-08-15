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
        $page = $request->get('page', 1);
    
        // Produtos
        $productColumns = [
            'id','sku','external_name','slug','price','stock',
            'photo','brand_id','category_id','subcategory_id','childcategory_id'
        ];
    
        $products = Product::select($productColumns)
            ->when($search, fn($q) => $q
                ->where('external_name','LIKE',"%{$search}%")
                ->orWhere('sku','LIKE',"%{$search}%")
                ->orWhere('slug','LIKE',"%{$search}%")
            )
            ->orderBy('id','desc')
            ->paginate(10);
    
        // Remove todo o código de Uploads
        // $uploads = Upload::... (remover)
    
        return view('admin.products.index', compact('products','search'));
    }

    // ================== CREATE ==================
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $childcategories = Childcategory::all();

        return view('admin.products.create', compact('brands','categories','subcategories','childcategories'));
    }

    // ================== STORE ==================
    public function store(Request $request)
    {
        if ($request->has('upload_file')) {
            // ==== Upload genérico ====
            $request->validate([
                'upload_file' => 'required|file|max:10240',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $file = $request->file('upload_file');
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

            return redirect()->back()->with('success','Upload realizado com sucesso!');
        }

        // ==== Produto ====
        $request->validate([
            'sku'=>'required|string|max:255|unique:products,sku',
            'external_name'=>'required|string|max:255',
            'description'=>'nullable|string|max:1000',
            'price'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',
            'brand_id'=>'nullable|exists:brands,id',
            'category_id'=>'nullable|exists:categories,id',
            'subcategory_id'=>'nullable|exists:subcategories,id',
            'childcategory_id'=>'nullable|exists:childcategories,id',
            'photo'=>'nullable|image|max:10240',
            'gallery.*'=>'nullable|image|max:10240',
        ]);

        $data = $request->only(['sku','external_name','description','price','stock','brand_id','category_id','subcategory_id','childcategory_id']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->convertToWebp($request->file('photo'),'photo');
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $this->convertToWebp($image,'gallery');
            }
            $data['gallery'] = json_encode($galleryPaths);
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success','Produto criado com sucesso!');
    }

    // ================== EDIT ==================
    public function edit($id)
    {
        $item = Product::findOrFail($id);
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all(); // pega todas, não só da categoria atual
        $childcategories = Childcategory::all(); // pega todas, não só da subcategoria atual
    
        return view('admin.products.edit', compact('item', 'brands', 'categories', 'subcategories', 'childcategories'));
    }
    


    // ================== UPDATE ==================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku'=>'required|string|max:255|unique:products,sku,'.$product->id,
            'external_name'=>'required|string|max:255',
            'description'=>'nullable|string|max:1000',
            'price'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',
            'brand_id'=>'nullable|exists:brands,id',
            'category_id'=>'nullable|exists:categories,id',
            'subcategory_id'=>'nullable|exists:subcategories,id',
            'childcategory_id'=>'nullable|exists:childcategories,id',
            'photo'=>'nullable|image|max:10240',
            'gallery.*'=>'nullable|image|max:10240',
        ]);

        $data = $request->only(['sku','external_name','description','price','stock','brand_id','category_id','subcategory_id','childcategory_id']);

        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'),'photo');
        }

        if ($request->hasFile('gallery')) {
            $existingGallery = $product->gallery ? json_decode($product->gallery,true) : [];
            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $this->convertToWebp($image,'gallery');
            }
            $data['gallery'] = json_encode($existingGallery);
        }

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success','Produto atualizado com sucesso!');
    }

    // ================== DELETE GALLERY IMAGE ==================
    public function deleteGalleryImage(Request $request, $productId, $imageName)
    {
        $product = Product::findOrFail($productId);

        if (!$product->gallery) {
            return redirect()->back()->with('error','Produto não possui galeria.');
        }

        $gallery = json_decode($product->gallery,true);

        // Procura a imagem pelo nome
        $imagePath = null;
        foreach($gallery as $key => $img){
            if(basename($img) === $imageName){
                $imagePath = $img;
                unset($gallery[$key]);
                break;
            }
        }

        if($imagePath && Storage::disk('public')->exists($imagePath)){
            Storage::disk('public')->delete($imagePath);
        }

        // Atualiza a galeria no DB
        $product->gallery = json_encode(array_values($gallery));
        $product->save();

        return redirect()->back()->with('success','Imagem da galeria removida com sucesso!');
    }

    // ================== DELETE MAIN PHOTO ==================
    public function deletePhoto($productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
            $product->photo = null;
            $product->save();
            return redirect()->back()->with('success','Foto principal removida com sucesso!');
        }

        return redirect()->back()->with('error','Produto não possui foto principal.');
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
                foreach(json_decode($product->gallery,true) as $img) {
                    if (Storage::disk('public')->exists($img)) Storage::disk('public')->delete($img);
                }
            }
            $product->delete();
        }
        return redirect()->route('admin.products.index')->with('success','Produto excluído com sucesso!');
    }

    // ================== FUNÇÕES AUX ==================
    private function convertToWebp($image,$type)
    {
        $temp = $image->getRealPath();
        $ext = strtolower($image->getClientOriginalExtension());

        $imgRes = match($ext){
            'jpeg','jpg'=>imagecreatefromjpeg($temp),
            'png'=>imagecreatefrompng($temp),
            'gif'=>imagecreatefromgif($temp),
            'webp'=>imagecreatefromwebp($temp),
            default=>throw new \Exception('Formato de imagem não suportado')
        };

        ob_start();
        imagewebp($imgRes,null,50);
        $webpData = ob_get_clean();
        imagedestroy($imgRes);

        $dir = "products/{$type}/";
        $fileName = uniqid().'.webp';
        if (!Storage::disk('public')->exists($dir)) Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put("{$dir}{$fileName}",$webpData);

        return "{$dir}{$fileName}";
    }
}
