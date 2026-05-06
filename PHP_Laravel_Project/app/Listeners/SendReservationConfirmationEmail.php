<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Notifications\ReservationCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Envía el email de confirmación de recepción al usuario
 * cuando se crea una nueva reserva.
 *
 * Implementa ShouldQueue para no bloquear el request HTTP.
 */
final class SendReservationConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /** Cola dedicada para emails */
    public string $queue = 'emails';

    /** Reintentos si el SMTP falla */
    public int $tries = 3;

    /** Delay entre reintentos (segundos) */
    public int $backoff = 30;

    public function handle(ReservationCreated $event): void
    {
        $reservation = $event->reservation->loadMissing('space');

        \Illuminate\Support\Facades\Notification::route('mail', [
            $reservation->user_email => $reservation->user_name,
        ])->notify(new ReservationCreatedNotification($reservation));
    }

    /**
     * Manejo de fallos después de agotar reintentos.
     */
    public function failed(ReservationCreated $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('Error enviando email de creación de reserva', [
            'reservation_slug' => $event->reservation->slug,
            'user_email'       => $event->reservation->user_email,
            'error'            => $exception->getMessage(),
        ]);
    }
}
