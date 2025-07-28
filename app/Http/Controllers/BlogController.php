<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    // Exibe lista paginada de blogs ativos e publicados
    public function index()
    {
        $blogs = Blog::where('is_active', true)
                    ->whereNotNull('published_at')
                    ->orderByDesc('published_at')
                    ->paginate(10);

        return view('blogs.index', compact('blogs'));
    }

    // Exibe um blog específico pelo slug
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();

        return view('blogs.show', compact('blog'));
    }

    // Upload da imagem via Trumbowyg
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('blog_images', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'file' => $url,
            ]);
        }

        return response()->json(['success' => false], 400);
    }

    // Cria um novo blog (exemplo básico)
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:blogs,slug',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $blog = Blog::create($data);

        return redirect()->route('blogs.show', $blog->slug)
                         ->with('success', 'Blog criado com sucesso!');
    }

    // Atualiza um blog e remove imagens deletadas do storage
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);
    
        $oldContent = $blog->content;
        $newContent = $request->input('content'); // pega o campo correto
    
        // Extrai URLs das imagens do conteúdo antigo e novo
        $oldImages = $this->extractImageUrls($oldContent);
        $newImages = $this->extractImageUrls($newContent);
    
        // Identifica imagens removidas
        $removedImages = array_diff($oldImages, $newImages);
    
        foreach ($removedImages as $imgUrl) {
            $path = parse_url($imgUrl, PHP_URL_PATH);
            $relativePath = str_replace('/storage/', '', $path);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    
        // Atualiza o conteúdo
        $blog->content = $newContent;
        $blog->save();
    
        return redirect()->back()->with('success', 'Blog atualizado com sucesso!');
    }
    
    private function extractImageUrls($html)
    {
        $imageUrls = [];
        if (!$html) return $imageUrls;
    
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $images = $dom->getElementsByTagName('img');
    
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            if ($src) {
                $imageUrls[] = $src;
            }
        }
    
        return $imageUrls;
    }
    

    // Exclui um blog e remove todas as imagens usadas no conteúdo
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        $images = $this->extractImageUrls($blog->content);

        foreach ($images as $imgUrl) {
            $path = parse_url($imgUrl, PHP_URL_PATH);
            $relativePath = ltrim(str_replace('/storage/', '', $path), '/');

            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $blog->delete();

        return redirect()->route('blogs.index')->with('success', 'Blog excluído com sucesso!');
    }
}
