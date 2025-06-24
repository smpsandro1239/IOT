<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FirmwareController;

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
Route::prefix('v1')->middleware('auth:sanctum')->group(function () { // Proteger todo o grupo v1 com Sanctum
    // Endpoint for ESP32 to register access events
    Route::post('/access-logs', [AccessLogController::class, 'store'])->name('api.accesslogs.store');

    // Endpoint for ESP32 to validate vehicle authorization
    Route::get('/vehicles/authorize/{lora_id}', [VehicleController::class, 'checkAuthorization'])->name('api.vehicles.authorize');

    // Endpoints for ESP32 OTA Firmware Updates
    Route::get('/firmware/check', [FirmwareController::class, 'checkForUpdate'])->name('api.firmware.check');
    // Usar {firmware} para Route Model Binding se o ID for o primary key.
    // Se o ESP32 for enviar a VERSÃO para download, precisaremos de uma rota diferente ou lógica no controller.
    // Por simplicidade, vamos assumir que o ESP32 recebe um ID (ou nome de arquivo único) do endpoint /check.
    // O plano original diz {firmware_id}, então vamos manter assim.
    Route::get('/firmware/download/{firmware}', [FirmwareController::class, 'download'])->name('api.firmware.download');
});
