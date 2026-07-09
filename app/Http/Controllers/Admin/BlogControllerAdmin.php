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
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $statusFilter = $request->get('status_filter');
        $sortBy = $request->get('sort_by');
        $perPage = $request->get('per_page', 30);

        $blogs = Blog::with('category')
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('subtitle', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            }))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($statusFilter, function ($q) use ($statusFilter) {
                if ($statusFilter === 'active') {
                    $q->where('is_active', 1);
                } elseif ($statusFilter === 'draft') {
                    $q->where('is_active', 0);
                }
            })
            ->when(
                $sortBy,
                function ($q) use ($sortBy) {
                    switch ($sortBy) {
                        case 'oldest':
                            $q->orderBy('created_at', 'asc');
                            break;
                        case 'title_az':
                            $q->orderBy('title', 'asc');
                            break;
                        case 'title_za':
                            $q->orderBy('title', 'desc');
                            break;
                        default:
                            $q->orderBy('created_at', 'desc');
                            break;
                    }
                },
                fn($q) => $q->orderBy('created_at', 'desc'),
            )
            ->paginate($perPage)
            ->appends($request->query());

        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateBlog($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->processImage($request->file('image'));
        }

        $data['gallery'] = $this->processGalleryUploads($request);

        $blog = Blog::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog criado com sucesso!',
                'redirect' => route('admin.blogs.edit', $blog),
            ]);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog criado com sucesso!');
    }

    public function edit(Blog $blog)
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validateBlog($request, $blog->id);

        $oldImages = $this->extractImageUrls($blog->content);
        $newImages = $this->extractImageUrls($data['content']);
        $removedImages = array_diff($oldImages, $newImages);
        foreach ($removedImages as $url) {
            $this->deleteImageByUrl($url);
        }

        if ($request->hasFile('image')) {
            if ($blog->image) Storage::disk('public')->delete($blog->image);
            $data['image'] = $this->processImage($request->file('image'));
        }

        $keptGallery = array_values(array_filter((array) $request->input('gallery_actual', [])));
        foreach (array_diff($blog->gallery ?? [], $keptGallery) as $removed) {
            Storage::disk('public')->delete($removed);
        }
        $data['gallery'] = array_merge($keptGallery, $this->processGalleryUploads($request));

        $blog->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog atualizado com sucesso!',
                'blog' => [
                    'slug' => $blog->slug,
                    'updated_at' => $blog->updated_at->format('d/m/Y H:i'),
                    'image_url' => $blog->image ? Storage::url($blog->image) : null,
                    'gallery' => collect($blog->gallery ?? [])->map(fn($path) => [
                        'path' => $path,
                        'url' => Storage::url($path),
                    ])->values(),
                ],
            ]);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog atualizado com sucesso!');
    }

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

    public function show(Blog $blog)
    {
        return response()->json($blog->load('category'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $path = $this->processImage($request->file('file'), 'blogs/content');

        return response()->json(['location' => Storage::url($path)]);
    }

    private function validateBlog(Request $request, $id = null)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:2000',
            'slug' => 'nullable|unique:blogs,slug' . ($id ? ",$id" : ''),
            'image' => 'nullable|image',
            'image_caption' => 'nullable|string|max:255',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'read_time' => 'nullable|integer|min:0|max:999',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
            'category_id' => 'required|exists:blog_categories,id',
            'gallery' => 'nullable|array|max:12',
            'gallery.*' => 'nullable|image|max:4096',
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['featured'] = $request->has('featured') ? 1 : 0;
        $data['published_at'] = $data['published_at'] ?? null;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }

    private function processGalleryUploads(Request $request)
    {
        if (!$request->hasFile('gallery')) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn($file) => $this->processImage($file, 'blogs/gallery'),
            $request->file('gallery'),
        )));
    }

    private function processImage($file, $folder = 'blogs')
    {
        $originalPath = $file->store($folder, 'public');
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
        if (!$path) {
            return;
        }

        $relative = ltrim(str_replace('/storage/', '', $path), '/');

        if (str_contains($relative, '..') || !str_starts_with($relative, 'blogs/')) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        } catch (\Throwable $e) {
            // Ignora URLs malformadas/externas — nunca deve bloquear a exclusão do blog.
        }
    }
}
