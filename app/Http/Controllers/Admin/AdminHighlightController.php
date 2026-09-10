<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Services\StorefrontLayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminHighlightController extends Controller
{
    public function index()
    {
        $settings = Generalsetting::firstOrCreate([], ['site_name' => 'SAX']);
        $sections = $settings->resolvedHomeSections();

        $layouts = StorefrontLayoutService::LAYOUTS;

        return view('admin.sections_home.index', compact('settings', 'sections', 'layouts'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::firstOrCreate([], ['site_name' => 'SAX']);
        $sectionKeys = array_keys(Generalsetting::HOME_SECTIONS);

        $validated = $request->validate([
            'storefront_layout' => ['required', Rule::in(array_keys(StorefrontLayoutService::LAYOUTS))],
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
            'sections.*.category_limit' => ['nullable', Rule::in(Generalsetting::CATEGORY_LIMIT_OPTIONS)],
            'sections.*.items' => ['nullable', 'array', 'size:3'],
            'sections.*.items.*' => ['array:pt,en,es'],
            'sections.*.items.*.pt' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.en' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.es' => ['nullable', 'string', 'max:100'],
        ]);

        $submitted = collect($validated['sections'])
            ->keyBy('key')
            ->sortBy('position');

        $homeSections = [];
        foreach ($sectionKeys as $key) {
            abort_unless($submitted->has($key), 422, "Configuração ausente para a seção {$key}.");
        }

        foreach ($submitted->values() as $index => $section) {
            $configuredSection = [
                'enabled' => (bool) $section['enabled'],
                'position' => $index + 1,
                'content' => collect(['pt', 'en', 'es'])->mapWithKeys(fn (string $language) => [
                    $language => [
                        'title' => trim((string) ($section['content'][$language]['title'] ?? '')),
                        'description' => trim((string) ($section['content'][$language]['description'] ?? '')),
                    ],
                ])->all(),
            ];

            if ($section['key'] === 'categories') {
                $configuredSection['category_limit'] = (string) ($section['category_limit'] ?? 'all');
            }

            if ($section['key'] === 'help') {
                $configuredSection['items'] = collect($section['items'] ?? Generalsetting::HOME_HELP_ITEMS)
                    ->map(fn (array $item): array => collect(['pt', 'en', 'es'])->mapWithKeys(fn (string $language): array => [
                        $language => trim((string) ($item[$language] ?? '')),
                    ])->all())
                    ->all();
            }

            $homeSections[$section['key']] = $configuredSection;
        }

        $settings->storefront_layout = $validated['storefront_layout'];
        $settings->home_sections = $homeSections;

        // Mantém compatibilidade com os controles antigos ainda usados em outras telas.
        $settings->show_highlight_lancamentos = $homeSections['recent_products']['enabled'];
        $settings->show_highlight_famosos = $homeSections['most_viewed']['enabled'];
        $settings->show_highlight_destaque = $homeSections['featured_products']['enabled'];

        $settings->save();

        Cache::forget('general_settings');
        app(StorefrontLayoutService::class)->clear();

        return redirect()->back()->with('success', 'Layout, ordem e visibilidade da Home atualizados!');
    }
}
