<?php

declare(strict_types=1);

namespace App\Events;

use App\Domain\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ReservationStatusChanged
 *
 * Se dispara cuando un administrador cambia el estado de una reserva:
 *   pendiente  → confirmada  (accept)
 *   pendiente  → rechazada   (reject)
 *   confirmada → cancelada   (cancel)
 *
 * Listeners sugeridos:
 *   - SendStatusChangedNotification  → email al usuario con el nuevo estado
 *   - LogReservationActivity         → auditoría de cambios
 */
final class ReservationStatusChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        /** Reserva con el estado ya actualizado */
        public readonly Reservation $reservation,
        /** Estado anterior para comparación en los listeners */
        public readonly string      $previousStatus,
    ) {}

    /**
     * Determina si el estado fue confirmado.
     */
    public function wasConfirmed(): bool
    {
        return $this->reservation->status === Reservation::STATUS_CONFIRMADA
            && $this->previousStatus      === Reservation::STATUS_PENDIENTE;
    }

    /**
     * Determina si el estado fue rechazado.
     */
    public function wasRejected(): bool
    {
        return $this->reservation->status === Reservation::STATUS_RECHAZADA;
    }

    /**
     * Determina si el estado fue cancelado.
     */
    public function wasCancelled(): bool
    {
        return $this->reservation->status === Reservation::STATUS_CANCELADA;
    }
}
