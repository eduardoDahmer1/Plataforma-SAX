<?php

namespace App\Http\Controllers;

use App\Models\SiteAnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteAnalyticsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (!Cache::remember('site_analytics_table_exists', 60, fn () => Schema::hasTable('site_analytics_events'))) {
            return response()->json(null, 204);
        }

        $data = $request->validate([
            'event_type' => ['required', 'in:page_view,click'],
            'visitor_id' => ['required', 'string', 'max:100'],
            'path' => ['required', 'string', 'max:500'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'max:500'],
            'element_text' => ['nullable', 'string', 'max:160'],
            'device_type' => ['nullable', 'in:desktop,tablet,mobile'],
            'referrer_host' => ['nullable', 'string', 'max:255'],
        ]);

        $path = '/' . ltrim(parse_url($data['path'], PHP_URL_PATH) ?: '/', '/');
        if (Str::startsWith($path, ['/admin', '/analytics'])) {
            return response()->json(null, 204);
        }

        SiteAnalyticsEvent::create([
            'event_date' => now()->toDateString(),
            'event_type' => $data['event_type'],
            'visitor_hash' => hash_hmac('sha256', $data['visitor_id'], config('app.key')),
            'user_id' => $request->user()?->id,
            'path' => Str::limit($path, 500, ''),
            'page_title' => $data['page_title'] ?? null,
            'target' => $data['target'] ?? null,
            'element_text' => $data['element_text'] ?? null,
            'device_type' => $data['device_type'] ?? null,
            'referrer_host' => $data['referrer_host'] ?? null,
        ]);

        return response()->json(['stored' => true], 201);
    }
}
