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
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('api.login')->middleware('cors');

Route::prefix('v1')->middleware('cors')->group(function () {
    // Test endpoint
    Route::get('/test', function() {
        return response()->json(['status' => 'API funcionando!', 'timestamp' => now()]);
    });

    // Public endpoint for the dashboard to get the latest status
    Route::get('/status/latest', [\App\Http\Controllers\JsonController::class, 'getLatest'])->name('api.status.latest');

    // Public endpoint to get authorized MACs
    Route::get('/macs-autorizados', [\App\Http\Controllers\JsonController::class, 'getMacsAutorizados'])->name('api.macs.index');

    // Public endpoint to add authorized MAC addresses
    Route::post('/macs-autorizados', [\App\Http\Controllers\JsonController::class, 'storeMac'])->name('api.macs.store');
    Route::post('/macs-autorizados/bulk', [\App\Http\Controllers\JsonController::class, 'storeBulkMacs'])->name('api.macs.store.bulk');
    Route::get('/macs-autorizados/download', [\App\Http\Controllers\JsonController::class, 'downloadMacs'])->name('api.macs.download');
    Route::delete('/macs-autorizados/{mac}', [\App\Http\Controllers\JsonController::class, 'deleteMac'])->name('api.macs.destroy');

    // Public endpoint for the dashboard to get metrics
    Route::get('/metrics', 'MetricsController@getMetrics')->name('api.metrics');
    Route::get('/metrics/{mac}', 'MetricsController@getMacMetrics')->name('api.metrics.mac');

    // Usar middleware de desenvolvimento em vez de Sanctum para facilitar o desenvolvimento
    Route::middleware('auth.dev')->group(function () {
        Route::post('/macs-autorizados', [MacsAutorizadosController::class, 'store'])->name('api.macs.store');
        Route::delete('/macs-autorizados/{mac}', [MacsAutorizadosController::class, 'destroy'])->name('api.macs.destroy');
        // Endpoint for ESP32 to validate vehicle authorization
        Route::get('/vehicles/authorize', [VehicleController::class, 'checkAuthorization'])->name('api.vehicles.authorize');
        Route::post('/macs-autorizados/bulk', [MacsAutorizadosController::class, 'storeBulk'])->name('api.macs.store.bulk');

        // Endpoints for ESP32 OTA Firmware Updates
        Route::get('/firmware/check', [FirmwareController::class, 'checkForUpdate'])->name('api.firmware.check');
        Route::get('/firmware/download/{firmware}', [FirmwareController::class, 'download'])->name('api.firmware.download');

        // Endpoints para carregar dados para formulários dinâmicos
        Route::get('companies/{company_ids_str}/sites', [\App\Http\Controllers\Api\DataController::class, 'getSitesForCompanies'])->name('api.v1.sites_for_companies');
        Route::get('sites/{site_ids_str}/barriers', [\App\Http\Controllers\Api\DataController::class, 'getBarriersForSites'])->name('api.v1.barriers_for_sites');

        // Gate Control
        Route::post('/gate/control', [\App\Http\Controllers\GateController::class, 'control'])->name('api.gate.control');
    });
});
