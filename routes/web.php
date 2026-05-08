<?php

declare(strict_types=1);

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticación (Pública)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/health', fn() => response()->json(['status' => 'ok']));

/*
|--------------------------------------------------------------------------
| Sistema Cerrado — Requiere Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Vista principal después de login
    Route::get('/', [SpaceController::class, 'index'])->name('spaces.index');
    
    // Espacios y Reservas
    Route::get('/spaces/{space:slug}', [SpaceController::class, 'show'])->name('spaces.show');
    Route::get('/historial', [ReservationController::class, 'history'])->name('reservations.history');
    Route::get('/reservations/new', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])
        ->middleware('throttle:reservations')
        ->name('reservations.store');
    Route::get('/reservations/{slug}', [ReservationController::class, 'show'])->name('reservations.show');

    /*
    |--------------------------------------------------------------------------
    | Rutas Administrativas
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['verified']) // Se asume auth ya aplicado por el grupo padre
        ->group(function () {

            Route::get('/', Admin\DashboardController::class)->name('dashboard');
            Route::get('calendar', Admin\CalendarController::class)->name('calendar');

            Route::resource('spaces', Admin\SpaceController::class)
                ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
                ->names([
                    'index'   => 'spaces.index',
                    'create'  => 'spaces.create',
                    'store'   => 'spaces.store',
                    'edit'    => 'spaces.edit',
                    'update'  => 'spaces.update',
                    'destroy' => 'spaces.destroy',
                ]);

            Route::get('reservations', [Admin\ReservationController::class, 'index'])->name('reservations.index');
            Route::get('reservations/{slug}', [Admin\ReservationController::class, 'show'])->name('reservations.show');
            Route::put('reservations/{slug}', [Admin\ReservationController::class, 'update'])->name('reservations.update');
            Route::delete('reservations/{slug}', [Admin\ReservationController::class, 'destroy'])->name('reservations.destroy');
        });
});
