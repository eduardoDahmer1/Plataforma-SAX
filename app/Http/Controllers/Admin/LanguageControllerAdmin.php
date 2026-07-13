<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class LanguageControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $filtro = $request->query('filtro'); // 'faltando' = sem tradução em EN ou ES

        $languages = Language::orderBy('key')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('key', 'like', "%{$search}%")
                        ->orWhere('pt', 'like', "%{$search}%")
                        ->orWhere('en', 'like', "%{$search}%")
                        ->orWhere('es', 'like', "%{$search}%");
                });
            })
            ->when($filtro === 'faltando', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('en')->orWhere('en', '')
                        ->orWhereNull('es')->orWhere('es', '');
                });
            })
            ->paginate(50)
            ->withQueryString();

        $totalFaltando = Language::where(function ($q) {
            $q->whereNull('en')->orWhere('en', '')
                ->orWhereNull('es')->orWhere('es', '');
        })->count();

        return view('admin.languages.index', compact('languages', 'search', 'filtro', 'totalFaltando'));
    }

    public function create()
    {
        return view('admin.languages.create');
    }

    public function store(Request $request)
    {
        $dados = $this->validar($request);

        Language::create($dados);
        $this->clearLanguageCache();

        return redirect()->route('admin.languages.index')
            ->with('success', __('messages.traducao_criada'));
    }

    public function edit($id)
    {
        $language = Language::findOrFail($id);

        return view('admin.languages.edit', compact('language'));
    }

    /**
     * Salva a tradução. Responde JSON quando vem da edição em linha da listagem.
     */
    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        $dados = $this->validar($request, $language->id);

        $language->update($dados);
        $this->clearLanguageCache();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.traducao_atualizada'),
                'falta'   => $language->en === null || $language->en === ''
                    || $language->es === null || $language->es === '',
            ]);
        }

        return redirect()->route('admin.languages.index')
            ->with('success', __('messages.traducao_atualizada'));
    }

    public function destroy(Request $request, $id)
    {
        Language::findOrFail($id)->delete();
        $this->clearLanguageCache();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('messages.traducao_removida')]);
        }

        return back()->with('success', __('messages.traducao_removida'));
    }

    private function validar(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'key' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9_.]+$/i',
                Rule::unique('languages', 'key')->ignore($ignoreId),
            ],
            'pt' => 'required|string',
            'en' => 'nullable|string',
            'es' => 'nullable|string',
        ], [
            'key.regex' => __('messages.traducao_chave_formato'),
        ]);
    }

    /**
     * As traduções são carregadas de uma única entrada de cache no AppServiceProvider
     * (all_translations_db, 24h). Limpar outras chaves não surtia efeito: a edição só
     * aparecia no site quando o cache expirava sozinho.
     */
    private function clearLanguageCache(): void
    {
        Cache::forget('all_translations_db');
    }
}
