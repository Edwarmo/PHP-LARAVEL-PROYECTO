<?php

declare(strict_types=1);

namespace App\Application\Mail;

use App\Domain\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ReservationStatusChangedMail extends Mailable implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly Reservation $reservation,
        private readonly string $previousStatus,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->reservation->status) {
            Reservation::STATUS_CONFIRMADA => "¡Reserva confirmada en {$this->reservation->space->name}! 🎉",
            Reservation::STATUS_RECHAZADA  => "Tu solicitud de reserva en {$this->reservation->space->name} no pudo ser procesada",
            Reservation::STATUS_CANCELADA  => "Tu reserva en {$this->reservation->space->name} ha sido cancelada",
            default => 'Actualización de tu reserva',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $space      = $this->reservation->space;
        $viewData   = [
            'reservation' => $this->reservation,
            'space'       => $space,
        ];

        $view = match ($this->reservation->status) {
            Reservation::STATUS_CONFIRMADA => $this->confirmedContent($viewData),
            Reservation::STATUS_RECHAZADA  => $this->rejectedContent($viewData),
            Reservation::STATUS_CANCELADA  => $this->cancelledContent($viewData),
            default => (new Content(markdown: 'emails.reservation-created')),
        };

        return $view;
    }

    private function confirmedContent(array $data): Content
    {
        return new Content(
            markdown: 'emails.reservation-confirmed',
            with: $data + [
                'start'       => $this->reservation->start_time,
                'end'         => $this->reservation->end_time,
                'trackingUrl' => route('reservations.show', $this->reservation->slug),
            ],
        );
    }

    private function rejectedContent(array $data): Content
    {
        $reason = null;
        if ($this->reservation->notes && str_contains($this->reservation->notes, '[Rechazo]:')) {
            $parts  = explode('[Rechazo]:', $this->reservation->notes);
            $reason = trim(end($parts));
        }

        return new Content(
            markdown: 'emails.reservation-rejected',
            with: $data + [
                'reason'    => $reason,
                'browseUrl' => route('spaces.show', $this->reservation->space->slug),
            ],
        );
    }

    private function cancelledContent(array $data): Content
    {
        $reason = null;
        if ($this->reservation->notes && str_contains($this->reservation->notes, '[Cancelación]:')) {
            $parts  = explode('[Cancelación]:', $this->reservation->notes);
            $reason = trim(end($parts));
        }

        return new Content(
            markdown: 'emails.reservation-cancelled',
            with: $data + [
                'reason'    => $reason,
                'browseUrl' => route('spaces.index'),
            ],
        );
    }
}
