<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
            'message' => $created->count() . ' banner(es) adicionado(s).',
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
        ]);

        if (array_key_exists('link', $validated)) {
            $validated['link'] = filled($validated['link']) ? trim($validated['link']) : null;
        }

        $homeBanner->update($validated);
        Cache::forget('home_banners_active_v1');

        return response()->json([
            'message' => 'Banner atualizado.',
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
        $path = $homeBanner->image;
        $homeBanner->delete();

        if (str_starts_with($path, 'home-banners/')) {
            Storage::disk('public')->delete($path);
        }

        Cache::forget('home_banners_active_v1');

        return response()->json(['message' => 'Banner removido.']);
    }

    private function payload(HomeBanner $banner): array
    {
        return [
            'id' => $banner->id,
            'group' => $banner->group,
            'image_url' => $banner->image_url,
            'link' => $banner->link,
            'is_active' => $banner->is_active,
            'update_url' => route('admin.home-banners.update', $banner),
            'delete_url' => route('admin.home-banners.destroy', $banner),
        ];
    }
}
