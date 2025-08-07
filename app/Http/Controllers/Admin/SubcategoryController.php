<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with('category')->paginate(10);
        return view('admin.subcategories.index', compact('subcategories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'category_id']);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        Subcategory::create($data);

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria criada com sucesso.');
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::all();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        // Pega todos os dados possíveis
        $data = $request->only(['name', 'category_id']);
    
        // Flags pra saber se veio nova imagem
        $temNovaPhoto = $request->hasFile('photo') && $request->file('photo')->isValid();
        $temNovoBanner = $request->hasFile('banner') && $request->file('banner')->isValid();
    
        // Substitui a imagem antiga, se mandou nova
        if ($temNovaPhoto) {
            if ($subcategory->photo) {
                $this->deleteFileIfExists($subcategory->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }
    
        if ($temNovoBanner) {
            if ($subcategory->banner) {
                $this->deleteFileIfExists($subcategory->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }
    
        // Salva no banco, sem freio
        $subcategory->update($data);
    
        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria atualizada com sucesso.');
    }

    public function show(Subcategory $subcategory)
    {
        return view('admin.subcategories.show', compact('subcategory'));
    }

    public function deletePhoto(Subcategory $subcategory)
    {
        // Verifica se a subcategoria tem uma foto associada
        if ($subcategory->photo) {
            // Exclui a foto do storage
            $this->deleteFileIfExists($subcategory->photo);
            
            // Remove a referência da foto no banco de dados
            $subcategory->photo = null;
            $subcategory->save();
        }
        
        return back()->with('success', 'Foto excluída com sucesso.');
    }
    
    public function deleteBanner(Subcategory $subcategory)
    {
        // Verifica se a subcategoria tem um banner associado
        if ($subcategory->banner) {
            // Exclui o banner do storage
            $this->deleteFileIfExists($subcategory->banner);
            
            // Remove a referência do banner no banco de dados
            $subcategory->banner = null;
            $subcategory->save();
        }
        
        return back()->with('success', 'Banner excluído com sucesso.');
    }

    public function destroy($id)
    {
        // Encontrar a subcategoria pelo ID
        $subcategory = Subcategory::findOrFail($id);
        
        // Exclui as imagens associadas à subcategoria
        if ($subcategory->photo) {
            $this->deleteFileIfExists($subcategory->photo);
        }
    
        if ($subcategory->banner) {
            $this->deleteFileIfExists($subcategory->banner);
        }
    
        // Deletar a subcategoria
        $subcategory->delete();
        
        // Redirecionar de volta com uma mensagem de sucesso
        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria deletada com sucesso!');
    }

    // Método privado para evitar duplicação de código ao deletar arquivos
    private function deleteFileIfExists($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }

    private function convertToWebp($image, $type)
    {
        $tempPath = $image->getRealPath();
        $imageResource = null;
        $extension = strtolower($image->getClientOriginalExtension());

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $imageResource = imagecreatefromjpeg($tempPath);
                break;
            case 'png':
                $imageResource = imagecreatefrompng($tempPath);
                break;
            case 'gif':
                $imageResource = imagecreatefromgif($tempPath);
                break;
            default:
                throw new \Exception('Formato de imagem não suportado.');
        }

        if (!$imageResource) {
            throw new \Exception('Falha ao criar recurso de imagem.');
        }

        $directory = ($type === 'banner') ? 'subcategories/banner/' : 'subcategories/photo/';
        $filename = uniqid() . '.webp';
        $fullPath = storage_path("app/public/{$directory}{$filename}");
        
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        imagewebp($imageResource, $fullPath, 45);
        imagedestroy($imageResource);
        
        return "{$directory}{$filename}";

        $directory = ($type === 'banner') ? 'subcategories/banner/' : 'subcategories/photo/';
        $filename = uniqid() . '.webp';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put("{$directory}{$filename}", $webpData);

        return "{$directory}{$filename}";
    }
}
