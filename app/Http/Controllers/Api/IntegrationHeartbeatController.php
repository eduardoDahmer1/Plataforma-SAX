<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IntegrationMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationHeartbeatController extends Controller
{
    public function __invoke(Request $request, IntegrationMonitorService $monitor): JsonResponse
    {
        $configuredToken = (string) config('services.integration_monitor.token');
        $receivedToken = (string) $request->bearerToken();

        if ($configuredToken === '') {
            Log::error('Integration monitor endpoint is disabled because its token is not configured.');
            return response()->json(['message' => __('messages.integration_monitor_not_configured')], 503);
        }

        if ($receivedToken === '' || !hash_equals($configuredToken, $receivedToken)) {
            return response()->json(['message' => __('messages.unauthorized')], 401);
        }

        $data = $request->validate([
            'source' => ['required', 'string', 'in:catalog'],
            'name' => ['nullable', 'string', 'max:100'],
            'event' => ['required', 'string', 'in:started,succeeded,failed'],
            'run_id' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9._:-]+$/'],
            'occurred_at' => ['required', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'error_code' => ['nullable', 'string', 'max:80'],
            'error_message' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
        ]);

        $state = $monitor->report($data);

        return response()->json([
            'received' => true,
            'status' => $state->status,
        ]);
    }
}
