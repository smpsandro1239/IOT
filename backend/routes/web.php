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

Route::get('/laravel-websockets', '\BeyondCode\LaravelWebSockets\Dashboard\Http\Controllers\DashboardController@index');
Route::get('/laravel-websockets/auth', '\BeyondCode\LaravelWebSockets\Dashboard\Http\Controllers\AuthorizeController@websocketAuth')
    ->name('laravel-websockets.auth')
    ->middleware('\BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize');

Route::get('/', function () {
    try {
        return view('welcome');
    } catch (\Exception $e) {
        // Fallback response if view rendering fails
        return response()->json([
            'status' => 'API Running',
            'message' => 'Welcome to the IoT Barrier Control System API',
            'frontend_url' => 'http://localhost:8080',
            'api_docs' => 'http://localhost:8000/api/documentation'
        ]);
    }
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Placeholder for Admin Dashboard Routes (authentication will be needed)
Route::prefix('admin')->middleware('auth')->group(function () { // TODO: Implementar middleware 'auth' real
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboard');

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

    // Access Log Routes
    Route::get('access-logs', [App\Http\Controllers\AccessLogController::class, 'index'])->name('admin.access-logs.index');

    // Firmware Management Routes
    Route::resource('firmwares', App\Http\Controllers\FirmwareController::class, ['as' => 'admin'])->except(['show']);
    Route::patch('firmwares/{firmware}/set-active', [App\Http\Controllers\FirmwareController::class, 'setActive'])->name('admin.firmwares.set-active');

    // API Token Management Routes
    Route::prefix('api-tokens')->name('admin.api-tokens.')->group(function () {
        Route::get('/', [App\Http\Controllers\ApiTokenController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ApiTokenController::class, 'store'])->name('store');
        Route::delete('/{tokenId}', [App\Http\Controllers\ApiTokenController::class, 'destroy'])->name('destroy');
    });

    // Company Management Routes
    Route::resource('companies', App\Http\Controllers\CompanyController::class, ['as' => 'admin'])->except(['show']);
    // 'show' pode ser adicionado depois se necessário, talvez para mostrar sites da empresa.

    // Site Management Routes
    Route::resource('sites', App\Http\Controllers\SiteController::class, ['as' => 'admin'])->except(['show']);

    // Barrier Management Routes
    Route::resource('barriers', App\Http\Controllers\BarrierController::class, ['as' => 'admin'])->except(['show']);

    // User Management Routes
    Route::resource('users', App\Http\Controllers\UserController::class, ['as' => 'admin'])->except(['show']);
});
