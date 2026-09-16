<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeBannerController extends Controller
{
    private const GROUPS = [HomeBanner::GROUP_MAIN, HomeBanner::GROUP_EDITORIAL];

    public function store(Request $request, string $group, ImageConverterService $converter)
    {
        abort_unless(in_array($group, self::GROUPS, true), 404);

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,avif', 'max:10240'],
        ]);

        $created = collect();
        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $group, $converter, &$created, &$storedPaths) {
                $nextOrder = ((int) HomeBanner::where('group', $group)->max('sort_order')) + 1;

                foreach ($validated['images'] as $image) {
                    $path = $converter->toWebp($image, "home-banners/{$group}", [
                        'quality' => 90,
                        'strict' => true,
                    ]);
                    $storedPaths[] = $path;
                    $created->push(HomeBanner::create([
                        'group' => $group,
                        'image' => $path,
                        'sort_order' => $nextOrder++,
                        'is_active' => true,
                    ]));
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            report($exception);

            return response()->json(['message' => 'Não foi possível processar as imagens.'], 422);
        }

        Cache::forget('home_banners_active_v1');

        return response()->json([
            'message' => $created->count().' banner(es) adicionado(s).',
            'items' => $created->map(fn (HomeBanner $banner) => $this->payload($banner))->values(),
        ], 201);
    }

    public function update(Request $request, HomeBanner $homeBanner)
    {
        $validated = $request->validate([
            'link' => ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (filled($value) && ! preg_match('#^(https?://|/)#i', $value)) {
                    $fail('Use uma URL completa (https://) ou um caminho iniciado por /.');
                }
            }],
            'is_active' => ['sometimes', 'boolean'],
            'title_pt' => ['nullable', 'string', 'max:160'],
            'description_pt' => ['nullable', 'string', 'max:600'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'description_en' => ['nullable', 'string', 'max:600'],
            'title_es' => ['nullable', 'string', 'max:160'],
            'description_es' => ['nullable', 'string', 'max:600'],
        ]);

        if (array_key_exists('link', $validated)) {
            $validated['link'] = filled($validated['link']) ? trim($validated['link']) : null;
        }

        foreach (['title_pt', 'description_pt', 'title_en', 'description_en', 'title_es', 'description_es'] as $field) {
            if (array_key_exists($field, $validated)) {
                $validated[$field] = filled($validated[$field]) ? trim($validated[$field]) : null;
            }
        }

        $homeBanner->update($validated);
        Cache::forget('home_banners_active_v1');

        return response()->json([
            'message' => 'Banner atualizado.',
            'item' => $this->payload($homeBanner->fresh()),
        ]);
    }

    public function updateImages(Request $request, HomeBanner $homeBanner, ImageConverterService $converter)
    {
        $validated = $request->validate([
            'desktop_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,avif', 'max:10240', 'required_without_all:mobile_image,remove_mobile_image'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,avif', 'max:10240', 'required_without_all:desktop_image,remove_mobile_image'],
            'remove_mobile_image' => ['nullable', 'boolean'],
        ]);

        $oldPaths = [];
        $newPaths = [];

        try {
            if ($request->hasFile('desktop_image')) {
                $newPaths['image'] = $converter->toWebp($request->file('desktop_image'), "home-banners/{$homeBanner->group}/desktop", [
                    'quality' => 90,
                    'strict' => true,
                ]);
                if (str_starts_with((string) $homeBanner->image, 'home-banners/')) {
                    $oldPaths[] = $homeBanner->image;
                }
            }

            if ($request->hasFile('mobile_image')) {
                $newPaths['mobile_image'] = $converter->toWebp($request->file('mobile_image'), "home-banners/{$homeBanner->group}/mobile", [
                    'quality' => 90,
                    'strict' => true,
                ]);
                if (str_starts_with((string) $homeBanner->mobile_image, 'home-banners/')) {
                    $oldPaths[] = $homeBanner->mobile_image;
                }
            } elseif (($validated['remove_mobile_image'] ?? false) && filled($homeBanner->mobile_image)) {
                if (str_starts_with((string) $homeBanner->mobile_image, 'home-banners/')) {
                    $oldPaths[] = $homeBanner->mobile_image;
                }
                $newPaths['mobile_image'] = null;
            }

            $homeBanner->update($newPaths);
            Storage::disk('public')->delete(array_values(array_unique($oldPaths)));
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete(array_filter($newPaths));
            report($exception);

            return response()->json(['message' => 'Não foi possível atualizar as imagens deste banner.'], 422);
        }

        Cache::forget('home_banners_active_v1');

        return response()->json([
            'message' => 'Imagens do banner atualizadas.',
            'item' => $this->payload($homeBanner->fresh()),
        ]);
    }

    public function reorder(Request $request, string $group)
    {
        abort_unless(in_array($group, self::GROUPS, true), 404);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => [Rule::exists('home_banners', 'id')->where(fn ($query) => $query->where('group', $group))],
        ]);

        DB::transaction(function () use ($validated, $group) {
            foreach (array_values($validated['items']) as $position => $id) {
                HomeBanner::where('group', $group)->whereKey($id)->update(['sort_order' => $position]);
            }
        });

        Cache::forget('home_banners_active_v1');

        return response()->json(['message' => 'Ordem atualizada.']);
    }

    public function destroy(HomeBanner $homeBanner)
    {
        $paths = [$homeBanner->image, $homeBanner->mobile_image];
        $homeBanner->delete();

        Storage::disk('public')->delete(array_values(array_filter(
            $paths,
            fn ($path) => str_starts_with((string) $path, 'home-banners/')
        )));

        Cache::forget('home_banners_active_v1');

        return response()->json(['message' => 'Banner removido.']);
    }

    private function payload(HomeBanner $banner): array
    {
        return [
            'id' => $banner->id,
            'group' => $banner->group,
            'image_url' => $banner->image_url,
            'mobile_image_url' => $banner->mobile_image_url,
            'has_mobile_image' => filled($banner->mobile_image),
            'link' => $banner->link,
            'is_active' => $banner->is_active,
            'title_pt' => $banner->title_pt,
            'description_pt' => $banner->description_pt,
            'title_en' => $banner->title_en,
            'description_en' => $banner->description_en,
            'title_es' => $banner->title_es,
            'description_es' => $banner->description_es,
            'update_url' => route('admin.home-banners.update', $banner),
            'images_url' => route('admin.home-banners.images', $banner),
            'delete_url' => route('admin.home-banners.destroy', $banner),
        ];
    }
}
