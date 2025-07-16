<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FirmwareController;
use App\Http\Controllers\MacsAutorizadosController;

/*
|------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API for ESP32 Base Station
Route::prefix('v1')->group(function () {
    // Public endpoint for the dashboard to get the latest status
    Route.::get('/status/latest', [AccessLogController::class, 'getLatest'])->name('api.status.latest');

    // Public endpoint for ESP32 to register access events
    Route::post('/access-logs', [AccessLogController::class, 'store'])->name('api.accesslogs.store');

    // Public endpoint to add authorized MAC addresses
    Route::post('/macs-autorizados', [MacsAutorizadosController::class, 'store'])->name('api.macs.store');
    Route::post('/macs-autorizados/bulk', [MacsAutorizadosController::class, 'storeBulk'])->name('api.macs.store.bulk');
    Route::get('/macs-autorizados/download', [MacsAutorizadosController::class, 'download'])->name('api.macs.download');

    // Public endpoint for the dashboard to get metrics
    Route::get('/metrics', 'MetricsController@getMetrics')->name('api.metrics');
    Route::get('/metrics/{mac}', 'MetricsController@getMacMetrics')->name('api.metrics.mac');

    Route::middleware('auth:sanctum')->group(function () {
        // Endpoint for ESP32 to validate vehicle authorization
        Route::get('/vehicles/authorize', [VehicleController::class, 'checkAuthorization'])->name('api.vehicles.authorize');

        // Endpoints for ESP32 OTA Firmware Updates
        Route::get('/firmware/check', [FirmwareController::class, 'checkForUpdate'])->name('api.firmware.check');
        Route::get('/firmware/download/{firmware}', [FirmwareController::class, 'download'])->name('api.firmware.download');

        // Endpoints para carregar dados para formulários dinâmicos
        Route::get('companies/{company_ids_str}/sites', [\App\Http\Controllers\Api\DataController::class, 'getSitesForCompanies'])->name('api.v1.sites_for_companies');
        Route::get('sites/{site_ids_str}/barriers', [\App\Http\Controllers\Api\DataController::class, 'getBarriersForSites'])->name('api.v1.barriers_for_sites');
    });
});
