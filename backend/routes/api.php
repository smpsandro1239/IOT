<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\VehicleController;
// Use App\Http\Controllers\FirmwareController; // Uncomment when controller is created

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
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
// TODO: Add API authentication middleware (e.g., Sanctum token or a simple shared secret)
Route::prefix('v1')->group(function () { // Versioning the API
    // Endpoint for ESP32 to register access events
    Route::post('/access-logs', [AccessLogController::class, 'store'])->name('api.accesslogs.store');

    // Endpoint for ESP32 to validate vehicle authorization
    Route::get('/vehicles/authorize/{lora_id}', [VehicleController::class, 'checkAuthorization'])->name('api.vehicles.authorize');

    // Endpoints for ESP32 OTA Firmware Updates (placeholders for Fase 4)
    // Route::get('/firmware/check', [FirmwareController::class, 'checkForUpdate'])->name('api.firmware.check');
    // Route::get('/firmware/download/{firmware_id}', [FirmwareController::class, 'download'])->name('api.firmware.download');
});
