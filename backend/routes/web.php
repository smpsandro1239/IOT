<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome'); // Assume a 'welcome' view will exist or be created later
});

// Placeholder for Admin Dashboard Routes (authentication will be needed)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return "Admin Dashboard"; // Placeholder
    })->name('admin.dashboard');

    // Vehicle Management Routes
    Route::resource('vehicles', App\Http\Controllers\VehicleController::class)->names([
        'index' => 'admin.vehicles.index',
        'create' => 'admin.vehicles.create',
        'store' => 'admin.vehicles.store',
        'show' => 'admin.vehicles.show',
        'edit' => 'admin.vehicles.edit',
        'update' => 'admin.vehicles.update',
        'destroy' => 'admin.vehicles.destroy',
    ]);

    // Access Log Routes (placeholders)
    // Route::get('access-logs', [App\Http\Controllers\AccessLogController::class, 'index'])->name('admin.access-logs.index');

    // Firmware Management Routes (placeholders)
    // Route::resource('firmwares', App\Http\Controllers\FirmwareController::class);
});
