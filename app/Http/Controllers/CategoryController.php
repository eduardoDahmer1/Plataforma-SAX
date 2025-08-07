<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
    
        if ($search) {
            $categories = Category::where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orderBy('name')
                ->paginate(20);
        } else {
            $categories = Category::orderBy('name')->paginate(20);
        }
    
        return view('admin.categories.index', compact('categories'));
    }   
    public function create()
    {
        return view('admin.categories.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug', 'status', 'is_featured', 'is_customizable', 'presentation_position', 'ref_code', 'is_customizable_number', 'link');

        // Verificar e converter a imagem 'photo'
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoPath = $this->convertToWebp($photo, 'photo');
            $data['photo'] = $photoPath;
        }

        // Verificar e converter o 'banner'
        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $banner = $request->file('banner');
            $bannerPath = $this->convertToWebp($banner, 'banner');
            $data['banner'] = $bannerPath;
        }

        // Cria a nova categoria no banco
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug', 'status', 'is_featured', 'is_customizable', 'presentation_position', 'ref_code', 'is_customizable_number', 'link');

        // Verificar e converter a imagem 'photo'
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Apaga a imagem antiga, se existir
            if ($category->photo && Storage::disk('public')->exists($category->photo)) {
                Storage::disk('public')->delete($category->photo);
            }

            $photo = $request->file('photo');
            $photoPath = $this->convertToWebp($photo, 'photo');
            $data['photo'] = $photoPath;
        }

        // Verificar e converter o 'banner'
        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            // Apaga o banner antigo, se existir
            if ($category->banner && Storage::disk('public')->exists($category->banner)) {
                Storage::disk('public')->delete($category->banner);
            }

            $banner = $request->file('banner');
            $bannerPath = $this->convertToWebp($banner, 'banner');
            $data['banner'] = $bannerPath;
        }

        // Atualiza a categoria no banco
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // Função para converter a imagem para WebP
    // Função para converter a imagem para WebP
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

        ob_start();
        imagewebp($imageResource, null, 45); // 80 é a qualidade da imagem WebP
        $webpData = ob_get_clean();
        imagedestroy($imageResource);

        // Escolher o diretório correto com base no tipo de imagem (photo ou banner)
        $directory = ($type == 'banner') ? 'categories/banner/' : 'categories/photo/';
        $filename = uniqid() . '.webp'; // Gera um nome único para a imagem

        // Verifica se o diretório existe, caso contrário, cria
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Salva a imagem no diretório correto
        Storage::disk('public')->put("{$directory}{$filename}", $webpData);

        return "{$directory}{$filename}";
    }

    // Função para excluir a foto (imagem WebP)
    public function deletePhoto(Category $category)
    {
        // Apaga a foto, se existir
        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
            $category->photo = null;
            $category->save();
        }

        return response()->json(['message' => 'Foto excluída com sucesso!'], 200);
    }

    // Frontend - listar categorias
    public function publicIndex()
    {
        $categories = Category::orderBy('name')->paginate(12);
        return view('categories.index', compact('categories'));
    }

    // Frontend - Exibir uma categoria específica
    public function publicShow($id)
    {
        $category = Category::findOrFail($id); // Encontra a categoria pelo ID

        return view('categories.show', compact('category')); // Exibe a categoria no frontend
    }

    // Função para excluir o banner (imagem WebP)
    public function deleteBanner(Category $category)
    {
        // Apaga o banner, se existir
        if ($category->banner && Storage::disk('public')->exists($category->banner)) {
            Storage::disk('public')->delete($category->banner);
            $category->banner = null;
            $category->save();
        }

        return response()->json(['message' => 'Banner excluído com sucesso!'], 200);
    }

    public function destroy($id)
    {
        // Encontrar a categoria pelo ID
        $category = Category::findOrFail($id);

        // Deletar a categoria
        $category->delete();

        // Redirecionar de volta com uma mensagem de sucesso
        return redirect()->route('admin.categories.index')->with('success', 'Categoria deletada com sucesso!');
    }
}