<?php

declare(strict_types=1);

use App\Http\Controllers\Admin;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas — Área sin autenticación
|--------------------------------------------------------------------------
*/

Route::get('/', [SpaceController::class, 'index'])->name('spaces.index');

Route::get('/spaces/{space:slug}', [SpaceController::class, 'show'])->name('spaces.show');

Route::get('/reservations/new', [ReservationController::class, 'create'])->name('reservations.create');

Route::get('/historial', [ReservationController::class, 'history'])->name('reservations.history');

Route::post('/reservations', [ReservationController::class, 'store'])
    ->middleware('throttle:reservations')
    ->name('reservations.store');

Route::get('/reservations/{slug}', [ReservationController::class, 'show'])->name('reservations.show');

/*
|--------------------------------------------------------------------------
| Rutas Administrativas — Middleware: auth + verified
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // ── Dashboard ─────────────────────────────────────────────
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // ── Salas (CRUD completo) ─────────────────────────────────
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

        // ── Reservas (listado + detalle) ──────────────────────────
        Route::get('reservations', [Admin\ReservationController::class, 'index'])
            ->name('reservations.index');

        Route::get('reservations/{slug}', [Admin\ReservationController::class, 'show'])
            ->name('reservations.show');

        // ── Acciones de Estado ────────────────────────────────────
        Route::post('reservations/{slug}/accept', [Admin\ReservationController::class, 'accept'])
            ->name('reservations.accept');

        Route::post('reservations/{slug}/reject', [Admin\ReservationController::class, 'reject'])
            ->name('reservations.reject');

        Route::post('reservations/{slug}/cancel', [Admin\ReservationController::class, 'cancel'])
            ->name('reservations.cancel');

        // ── Calendario Semanal ────────────────────────────────────
        Route::get('calendar', Admin\CalendarController::class)->name('calendar');
    });
