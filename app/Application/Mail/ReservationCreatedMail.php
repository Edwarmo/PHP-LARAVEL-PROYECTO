<?php

declare(strict_types=1);

namespace App\Application\Mail;

use App\Domain\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ReservationCreatedMail extends Mailable implements ShouldQueue
{
    public string $queue = 'emails';
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu reserva en {$this->reservation->space->name} ha sido recibida ✅",
        );
    }

    public function content(): Content
    {
        $space       = $this->reservation->space;
        $slug        = $this->reservation->slug;
        $trackingUrl = route('reservations.show', $slug);

        return new Content(
            markdown: 'emails.reservation-created',
            with: [
                'reservation' => $this->reservation,
                'space'       => $space,
                'start'       => $this->reservation->start_time,
                'end'         => $this->reservation->end_time,
                'trackingUrl' => $trackingUrl,
            ],
        );
    }
}
