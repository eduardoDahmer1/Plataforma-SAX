<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontFavicon;
use App\Services\StorefrontFaviconService;
use App\Services\StorefrontLayoutService;
use App\Services\FaviconImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorefrontFaviconController extends Controller
{
    public function update(Request $request, string $layout, StorefrontFaviconService $favicons, FaviconImageService $images): RedirectResponse|JsonResponse
    {
        $this->ensureAvailable($layout);
        $request->validate(['favicon' => ['required', 'file', 'max:5120']]);

        $oldPath = StorefrontFavicon::query()->where('layout', $layout)->value('path');
        $path = $images->convert($request->file('favicon'), $layout);
        try {
            StorefrontFavicon::query()->updateOrCreate(['layout' => $layout], ['path' => $path]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
        $favicons->clear($layout);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Ícone atualizado.', 'url' => $favicons->url($layout)]);
        }

        return back()->with('success', 'Ícone da aba atualizado.');
    }

    public function destroy(Request $request, string $layout, StorefrontFaviconService $favicons): RedirectResponse|JsonResponse
    {
        $this->ensureAvailable($layout);
        $record = StorefrontFavicon::query()->where('layout', $layout)->first();
        if ($record) {
            $path = $record->path;
            $record->delete();
            Storage::disk('public')->delete($path);
        }
        $favicons->clear($layout);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Ícone padrão restaurado.', 'url' => $favicons->url($layout)]);
        }

        return back()->with('success', 'Ícone padrão restaurado.');
    }

    private function ensureAvailable(string $layout): void
    {
        abort_unless(array_key_exists($layout, app(StorefrontLayoutService::class)->availableLayouts()), 403);
    }
}
