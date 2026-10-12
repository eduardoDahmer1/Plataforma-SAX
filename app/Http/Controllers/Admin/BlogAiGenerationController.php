<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BlogAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class BlogAiGenerationController extends Controller
{
    public function store(Request $request, BlogAiService $service): JsonResponse
    {
        $this->ensureExperimentIsEnabled();

        $data = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'focus' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $generated = $service->generate($data);
            Log::info('Experimental blog article generated with AI.', [
                'user_id' => $request->user()?->id,
                'source_urls' => collect($generated['sources'])->pluck('url')->all(),
                'model' => config('services.openai.model'),
            ]);

            return response()->json([
                'message' => 'Artículo generado. Revisá los campos y guardá el formulario.',
                'blog' => [
                    'title' => $generated['title'],
                    'subtitle' => $generated['subtitle'],
                    'content' => $service->contentWithSources($generated['content_html'], $generated['sources']),
                    'meta_description' => $generated['meta_description'],
                    'author' => $generated['author'],
                ],
                'sources' => $generated['sources'],
                'image_suggestions' => $generated['image_suggestions'],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible generar el artículo. Revisá la configuración e intentá nuevamente.',
            ], 500);
        }
    }

    private function ensureExperimentIsEnabled(): void
    {
        abort_unless(
            app()->environment(['local', 'staging'])
                && (bool) config('services.openai.blog_experiment_enabled'),
            404
        );
    }

}
