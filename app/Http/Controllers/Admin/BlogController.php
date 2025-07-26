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

                // Remove imagem original
                @unlink($fullPath);

                // Atualiza o caminho salvo no banco
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

                // Remove imagem original
                @unlink($fullPath);

                // Atualiza o caminho salvo no banco
                $data['image'] = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
            }
        }

        $blog->update($data);
        return redirect()->route('admin.blogs.index')->with('success', 'Blog atualizado com sucesso!');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->image) Storage::disk('public')->delete($blog->image);
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog removido com sucesso!');
    }
}
