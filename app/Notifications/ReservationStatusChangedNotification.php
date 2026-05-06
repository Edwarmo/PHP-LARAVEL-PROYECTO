<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación de cambio de estado de reserva.
 *
 * Lógica condicional según el nuevo estado:
 *   - confirmada → mensaje positivo + recordatorio de horario
 *   - rechazada  → motivo (si existe en notas) + invitación a otros horarios
 *   - cancelada  → confirmación de cancelación
 */
final class ReservationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Reservation $reservation,
        private readonly string      $previousStatus,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->reservation->status) {
            Reservation::STATUS_CONFIRMADA => $this->buildConfirmedMail(),
            Reservation::STATUS_RECHAZADA  => $this->buildRejectedMail(),
            Reservation::STATUS_CANCELADA  => $this->buildCancelledMail(),
            default => (new MailMessage)->subject('Actualización de tu reserva'),
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  ✅ Confirmada
    // ─────────────────────────────────────────────────────────────

    private function buildConfirmedMail(): MailMessage
    {
        $space       = $this->reservation->space;
        $trackingUrl = route('reservations.show', $this->reservation->slug);

        return (new MailMessage)
            ->subject("¡Reserva confirmada en {$space->name}! 🎉")
            ->markdown('emails.reservation-confirmed', [
                'reservation' => $this->reservation,
                'space'       => $space,
                'start'       => $this->reservation->start_time,
                'end'         => $this->reservation->end_time,
                'trackingUrl' => $trackingUrl,
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  ❌ Rechazada
    // ─────────────────────────────────────────────────────────────

    private function buildRejectedMail(): MailMessage
    {
        $space      = $this->reservation->space;
        $browseUrl  = route('spaces.show', $space->slug);

        // Extraer motivo del campo notes si contiene "[Rechazo]:"
        $reason = null;
        if ($this->reservation->notes && str_contains($this->reservation->notes, '[Rechazo]:')) {
            $parts  = explode('[Rechazo]:', $this->reservation->notes);
            $reason = trim(end($parts));
        }

        return (new MailMessage)
            ->subject("Tu solicitud de reserva en {$space->name} no pudo ser procesada")
            ->markdown('emails.reservation-rejected', [
                'reservation' => $this->reservation,
                'space'       => $space,
                'reason'      => $reason,
                'browseUrl'   => $browseUrl,
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  🚫 Cancelada
    // ─────────────────────────────────────────────────────────────

    private function buildCancelledMail(): MailMessage
    {
        $space     = $this->reservation->space;
        $browseUrl = route('spaces.index');

        // Extraer motivo del campo notes si contiene "[Cancelación]:"
        $reason = null;
        if ($this->reservation->notes && str_contains($this->reservation->notes, '[Cancelación]:')) {
            $parts  = explode('[Cancelación]:', $this->reservation->notes);
            $reason = trim(end($parts));
        }

        return (new MailMessage)
            ->subject("Tu reserva en {$space->name} ha sido cancelada")
            ->markdown('emails.reservation-cancelled', [
                'reservation' => $this->reservation,
                'space'       => $space,
                'reason'      => $reason,
                'browseUrl'   => $browseUrl,
            ]);
    }
}
