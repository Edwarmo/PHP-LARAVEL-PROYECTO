<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\ReservationCreated;
use App\Events\ReservationStatusChanged;
use App\Listeners\SendReservationConfirmationEmail;
use App\Listeners\SendReservationStatusChangedEmail;
use App\Services\AvailabilityService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar AvailabilityService como singleton
        // Lee RESERVATION_SLOT_MINUTES del .env una sola vez por ciclo de vida
        $this->app->singleton(AvailabilityService::class, function () {
            return AvailabilityService::make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Registro de Eventos y Listeners ───────────────────────
        Event::listen(
            ReservationCreated::class,
            SendReservationConfirmationEmail::class,
        );

        Event::listen(
            ReservationStatusChanged::class,
            SendReservationStatusChangedEmail::class,
        );

        // ── Rate Limiter para el endpoint de reservas ─────────────
        // Limita a 10 intentos por minuto por IP (protección anti-spam)
        RateLimiter::for('reservations', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos. Por favor espera un momento e inténtalo de nuevo.',
                    ], 429);
                });
        });
    }
}
