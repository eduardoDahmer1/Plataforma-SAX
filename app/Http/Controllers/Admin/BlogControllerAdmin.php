<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogControllerAdmin extends Controller
{
    // Lista blogs
    public function index()
    {
        $blogs = Blog::with('category')->latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs'));
    }

    // Form para criar blog
    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blogs.create', compact('categories'));
    }

    // Salva blog novo
    public function store(Request $request)
    {
        $data = $this->validateBlog($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->processImage($request->file('image'));
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog criado com sucesso!');
    }

    // Form para editar
    public function edit(Blog $blog)
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    // Atualiza blog
    public function update(Request $request, Blog $blog)
    {
        $data = $this->validateBlog($request, $blog->id);

        // Remove imagens antigas do conteúdo
        $oldImages = $this->extractImageUrls($blog->content);
        $newImages = $this->extractImageUrls($data['content']);
        $removedImages = array_diff($oldImages, $newImages);
        foreach ($removedImages as $url) {
            $this->deleteImageByUrl($url);
        }

        // Atualiza imagem principal
        if ($request->hasFile('image')) {
            if ($blog->image) Storage::disk('public')->delete($blog->image);
            $data['image'] = $this->processImage($request->file('image'));
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog atualizado com sucesso!');
    }

    // Remove blog
    public function destroy(Blog $blog)
    {
        if ($blog->image) Storage::disk('public')->delete($blog->image);

        $images = $this->extractImageUrls($blog->content);
        foreach ($images as $url) {
            $this->deleteImageByUrl($url);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog removido com sucesso!');
    }

    // Retorna blog JSON (útil para modal preview)
    public function show(Blog $blog)
    {
        return response()->json($blog->load('category'));
    }

    /*** Helpers ***/
    private function validateBlog(Request $request, $id = null)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:2000',
            'slug' => 'nullable|unique:blogs,slug' . ($id ? ",$id" : ''),
            'image' => 'nullable|image',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'category_id' => 'required|exists:blog_categories,id',
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['published_at'] = $data['published_at'] ?? null;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }

    private function processImage($file)
    {
        $originalPath = $file->store('blogs', 'public');
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

    private function extractImageUrls($html)
    {
        $urls = [];
        if (!$html) return $urls;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        foreach ($dom->getElementsByTagName('img') as $img) {
            $src = $img->getAttribute('src');
            if ($src) $urls[] = $src;
        }

        return $urls;
    }

    private function deleteImageByUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $relative = ltrim(str_replace('/storage/', '', $path), '/');
        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
