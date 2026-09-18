<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Generalsetting;
use App\Services\OpticalNavigationService;
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

        $layoutService = app(StorefrontLayoutService::class);
        $layouts = $layoutService->availableLayouts();
        $selectedLayout = $layoutService->effective();
        $opticalItems = app(OpticalNavigationService::class)->items();
        $saxCategories = Category::query()
            ->select(['id', 'name', 'slug', 'photo'])
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'key' => 'category:'.$category->id,
                'type' => 'category',
                'label' => $category->name,
                'image' => $this->mediaUrl($category->photo),
            ]);

        return view('admin.sections_home.index', compact('settings', 'sections', 'layouts', 'selectedLayout', 'opticalItems', 'saxCategories'));
    }

    public function update(Request $request)
    {
        $settings = Generalsetting::firstOrCreate([], ['site_name' => 'SAX']);
        $sectionKeys = array_keys(Generalsetting::HOME_SECTIONS);
        $layoutService = app(StorefrontLayoutService::class);
        $availableLayoutKeys = array_keys($layoutService->availableLayouts());

        if (count($availableLayoutKeys) === 1) {
            $request->merge(['storefront_layout' => $availableLayoutKeys[0]]);
        }

        $validated = $request->validate([
            'storefront_layout' => ['required', Rule::in($availableLayoutKeys)],
            'sections' => ['required', 'array'],
            'sections.*.key' => ['required', 'string', 'distinct', Rule::in($sectionKeys)],
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
            'sections.*.optical_item_keys' => ['nullable', 'array'],
            'sections.*.optical_item_keys.*' => ['string', 'max:60'],
            'sections.*.items' => ['nullable', 'array', 'size:3'],
            'sections.*.items.*' => ['array:pt,en,es'],
            'sections.*.items.*.pt' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.en' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.es' => ['nullable', 'string', 'max:100'],
        ], [
            'storefront_layout.in' => 'O layout escolhido não pertence a esta instalação.',
            'sections.required' => 'As configurações das seções não foram enviadas. Recarregue a página e tente novamente.',
            'sections.*.key.distinct' => 'Uma mesma seção foi enviada mais de uma vez. Recarregue a página e tente novamente.',
            'sections.*.position.integer' => 'A posição de uma das seções é inválida.',
            'sections.*.content.*.title.max' => 'O título pode ter no máximo 160 caracteres.',
            'sections.*.content.*.description.max' => 'A descrição pode ter no máximo 600 caracteres.',
            'sections.*.items.*.*.max' => 'O texto informativo pode ter no máximo 100 caracteres.',
        ]);

        $submitted = collect($validated['sections'])
            ->keyBy('key')
            ->sortBy('position');
        $allowedOpticalItemKeys = app(OpticalNavigationService::class)->items()
            ->map(fn (array $item): string => $item['type'].':'.$item['id'])
            ->flip();

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
                $configuredSection['optical_item_keys'] = $this->validOpticalItemKeys(
                    $section['optical_item_keys'] ?? [],
                    $allowedOpticalItemKeys
                );
            }

            if ($section['key'] === 'exclusive_collection') {
                $configuredSection['optical_item_keys'] = $this->validOpticalItemKeys(
                    $section['optical_item_keys'] ?? [],
                    $allowedOpticalItemKeys
                );
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
        $layoutService->clear();

        return redirect()->back()->with('success', 'Layout, ordem e visibilidade da Home atualizados!');
    }

    private function validOpticalItemKeys(array $keys, $allowedKeys): array
    {
        return collect($keys)
            ->filter(fn ($key): bool => is_string($key) && $allowedKeys->has($key))
            ->unique()
            ->values()
            ->all();
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return asset('storage/'.preg_replace('#^storage/#i', '', ltrim($path, '/')));
    }
}
