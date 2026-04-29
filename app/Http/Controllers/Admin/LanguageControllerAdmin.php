<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        // Captura o termo de busca
        $search = $request->query('search');

        $languages = Language::orderBy('key')
            ->when($search, function ($query, $search) {
                return $query->where('key', 'like', "%{$search}%")
                             ->orWhere('pt', 'like', "%{$search}%")
                             ->orWhere('en', 'like', "%{$search}%")
                             ->orWhere('es', 'like', "%{$search}%");
            })
            ->get();

        return view('admin.languages.index', compact('languages', 'search'));
    }

    public function create()
    {
        return view('admin.languages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|unique:languages,key',
            'pt'  => 'required',
        ]);

        Language::create($request->all());
        $this->clearLanguageCache();

        return redirect()->route('admin.languages.index')->with('success', 'Tradução criada!');
    }

    public function edit($id)
    {
        $language = Language::findOrFail($id);
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        $language->update($request->all());
        $this->clearLanguageCache();

        return redirect()->route('admin.languages.index')->with('success', 'Tradução atualizada!');
    }

    public function destroy($id)
    {
        Language::findOrFail($id)->delete();
        $this->clearLanguageCache();
        return back()->with('success', 'Removido com sucesso!');
    }

    private function clearLanguageCache()
    {
        Cache::forget('translations_pt_BR');
        Cache::forget('translations_en');
        Cache::forget('translations_es');
    }
}