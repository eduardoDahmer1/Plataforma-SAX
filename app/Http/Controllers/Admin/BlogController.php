<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'subtitle' => 'nullable',
            'slug' => 'nullable|unique:blogs',
            'image' => 'nullable|image',
            'content' => 'required',
            'published_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $originalPath = $request->file('image')->store('blogs', 'public');
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
                imagewebp($image, $webpPath, 20);
                imagedestroy($image);

                @unlink($fullPath);

                $data['image'] = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
            }
        }

        Blog::create($data);
        return redirect()->route('admin.blogs.index')->with('success', 'Blog criado com sucesso!');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title' => 'required',
            'subtitle' => 'nullable',
            'slug' => 'nullable|unique:blogs,slug,' . $blog->id,
            'image' => 'nullable|image',
            'content' => 'required',
            'published_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        // 🔥 Apagar imagens que foram removidas do campo content
        $oldImages = $this->extractImageUrls($blog->content);
        $newImages = $this->extractImageUrls($data['content']);
        $removedImages = array_diff($oldImages, $newImages);

        foreach ($removedImages as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $relative = ltrim(str_replace('/storage/', '', $path), '/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        }

        // Atualiza imagem principal se trocada
        if ($request->hasFile('image')) {
            if ($blog->image) Storage::disk('public')->delete($blog->image);

            $originalPath = $request->file('image')->store('blogs', 'public');
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
                imagewebp($image, $webpPath, 75);
                imagedestroy($image);
                @unlink($fullPath);
                $data['image'] = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
            }
        }

        $blog->update($data);
        return redirect()->route('admin.blogs.index')->with('success', 'Blog atualizado com sucesso!');
    }

    public function destroy(Blog $blog)
    {
        // Remove imagem principal
        if ($blog->image) Storage::disk('public')->delete($blog->image);

        // Remove imagens do campo content
        $images = $this->extractImageUrls($blog->content);
        foreach ($images as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $relative = ltrim(str_replace('/storage/', '', $path), '/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        }

        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog removido com sucesso!');
    }

    // ✅ Função auxiliar para extrair URLs de imagens no HTML
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
}
