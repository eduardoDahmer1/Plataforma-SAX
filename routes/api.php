<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntegrationHeartbeatController;
use App\Http\Controllers\Api\LocationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/integrations/catalog/heartbeat', IntegrationHeartbeatController::class)
    ->middleware('throttle:20,1')
    ->name('api.integrations.catalog.heartbeat');

Route::prefix('locations')->middleware('throttle:60,1')->group(function () {
    Route::get('/countries', [LocationController::class, 'countries'])->name('api.locations.countries');
    Route::get('/subdivisions', [LocationController::class, 'subdivisions'])->name('api.locations.subdivisions');
    Route::get('/cities', [LocationController::class, 'cities'])->name('api.locations.cities');
});
