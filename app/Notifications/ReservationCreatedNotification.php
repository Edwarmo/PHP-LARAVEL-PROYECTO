<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación enviada al usuario cuando su reserva es recibida.
 * Estado: pendiente — en espera de confirmación del administrador.
 */
final class ReservationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Reservation $reservation,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $space       = $this->reservation->space;
        $start       = $this->reservation->start_time;
        $end         = $this->reservation->end_time;
        $slug        = $this->reservation->slug;
        $trackingUrl = route('reservations.show', $slug);

        return (new MailMessage)
            ->subject("Tu reserva en {$space->name} ha sido recibida ✅")
            ->markdown('emails.reservation-created', [
                'reservation'  => $this->reservation,
                'space'        => $space,
                'start'        => $start,
                'end'          => $end,
                'trackingUrl'  => $trackingUrl,
            ]);
    }
}
