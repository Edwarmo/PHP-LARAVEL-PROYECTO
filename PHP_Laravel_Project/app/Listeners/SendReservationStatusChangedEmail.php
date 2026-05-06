<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationStatusChanged;
use App\Notifications\ReservationStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Envía el email de cambio de estado al usuario cuando un admin
 * confirma, rechaza o cancela su reserva.
 *
 * Implementa ShouldQueue para no bloquear el request HTTP del admin.
 */
final class SendReservationStatusChangedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int    $tries = 3;
    public int    $backoff = 30;

    public function handle(ReservationStatusChanged $event): void
    {
        $reservation = $event->reservation->loadMissing('space');

        // No enviar email para estados que no requieren notificación al usuario
        $notifiableStatuses = [
            \App\Models\Reservation::STATUS_CONFIRMADA,
            \App\Models\Reservation::STATUS_RECHAZADA,
            \App\Models\Reservation::STATUS_CANCELADA,
        ];

        if (! in_array($reservation->status, $notifiableStatuses, true)) {
            return;
        }

        Notification::route('mail', [
            $reservation->user_email => $reservation->user_name,
        ])->notify(new ReservationStatusChangedNotification($reservation, $event->previousStatus));
    }

    public function failed(ReservationStatusChanged $event, \Throwable $exception): void
    {
        Log::error('Error enviando email de cambio de estado de reserva', [
            'reservation_slug' => $event->reservation->slug,
            'new_status'       => $event->reservation->status,
            'previous_status'  => $event->previousStatus,
            'error'            => $exception->getMessage(),
        ]);
    }
}
