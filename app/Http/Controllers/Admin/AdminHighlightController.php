<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminHighlightController extends Controller
{
    public function index()
    {
        $settings = Generalsetting::firstOrCreate([], ['site_name' => 'SAX']);
        $sections = $settings->resolvedHomeSections();

        return view('admin.sections_home.index', compact('settings', 'sections'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::firstOrCreate([], ['site_name' => 'SAX']);
        $sectionKeys = array_keys(Generalsetting::HOME_SECTIONS);

        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.key' => ['required', 'string', Rule::in($sectionKeys)],
            'sections.*.enabled' => ['required', 'boolean'],
            'sections.*.position' => ['required', 'integer', 'min:1', 'max:100'],
            'sections.*.content' => ['required', 'array:pt,en,es'],
            'sections.*.content.pt.title' => ['nullable', 'string', 'max:160'],
            'sections.*.content.pt.description' => ['nullable', 'string', 'max:600'],
            'sections.*.content.en.title' => ['nullable', 'string', 'max:160'],
            'sections.*.content.en.description' => ['nullable', 'string', 'max:600'],
            'sections.*.content.es.title' => ['nullable', 'string', 'max:160'],
            'sections.*.content.es.description' => ['nullable', 'string', 'max:600'],
        ]);

        $submitted = collect($validated['sections'])
            ->keyBy('key')
            ->sortBy('position');

        $homeSections = [];
        foreach ($sectionKeys as $key) {
            abort_unless($submitted->has($key), 422, "Configuração ausente para a seção {$key}.");
        }

        foreach ($submitted->values() as $index => $section) {
            $homeSections[$section['key']] = [
                'enabled' => (bool) $section['enabled'],
                'position' => $index + 1,
                'content' => collect(['pt', 'en', 'es'])->mapWithKeys(fn (string $language) => [
                    $language => [
                        'title' => trim((string) ($section['content'][$language]['title'] ?? '')),
                        'description' => trim((string) ($section['content'][$language]['description'] ?? '')),
                    ],
                ])->all(),
            ];
        }

        $settings->home_sections = $homeSections;

        // Mantém compatibilidade com os controles antigos ainda usados em outras telas.
        $settings->show_highlight_lancamentos = $homeSections['recent_products']['enabled'];
        $settings->show_highlight_famosos = $homeSections['most_viewed']['enabled'];
        $settings->show_highlight_destaque = $homeSections['featured_products']['enabled'];

        $settings->save();

        Cache::forget('general_settings');

        return redirect()->back()->with('success', 'Ordem e visibilidade das seções da Home atualizadas!');
    }
}
