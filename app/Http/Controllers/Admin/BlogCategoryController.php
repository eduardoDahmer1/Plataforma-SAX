<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogCategoryController extends Controller
{
    // Index: lista todas as categorias
    public function index()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blogs.categories.index', compact('categories'));
    }

    // Create: mostra formulário para criar
    public function create()
    {
        return view('admin.blogs.categories.create');
    }

    // Store: salva nova categoria
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'nullable|image'
        ]);

        $data = $request->only('name');

        if ($request->hasFile('banner')) {
            $data['banner'] = $this->processImage($request->file('banner'));
        }

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')
                         ->with('success', 'Categoria criada com sucesso!');
    }

    // Edit: mostra formulário para editar
    public function edit(BlogCategory $category)
    {
        return view('admin.blogs.categories.edit', compact('category'));
    }

    // Update: salva alterações
    public function update(Request $request, BlogCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'nullable|image'
        ]);

        $data = $request->only('name');

        if ($request->hasFile('banner')) {
            // Remove banner antigo
            if ($category->banner) {
                Storage::disk('public')->delete($category->banner);
            }

            $data['banner'] = $this->processImage($request->file('banner'));
        }

        $category->update($data);

        return redirect()->route('admin.blog-categories.index')
                         ->with('success', 'Categoria atualizada com sucesso!');
    }

    // Show: exibe detalhes da categoria
    public function show(BlogCategory $category)
    {
        return view('admin.blogs.categories.show', compact('category'));
    }

    // Destroy: remove categoria
    public function destroy(BlogCategory $category)
    {
        if ($category->banner) {
            Storage::disk('public')->delete($category->banner);
        }

        $category->delete();

        return redirect()->route('admin.blog-categories.index')
                         ->with('success', 'Categoria removida com sucesso!');
    }

    // Função para processar e converter imagem para WebP
    private function processImage($file)
    {
        $originalPath = $file->store('blog_category_banners', 'public');
        $fullPath = storage_path('app/public/' . $originalPath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $image = null;
        if (in_array($extension, ['jpg', 'jpeg'])) {
            $image = imagecreatefromjpeg($fullPath);
        } elseif ($extension === 'png') {
            $image = imagecreatefrompng($fullPath);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        if ($image) {
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);
            imagewebp($image, $webpPath, 85);
            imagedestroy($image);
            @unlink($fullPath);

            return preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
        }

        return $originalPath;
    }
}
