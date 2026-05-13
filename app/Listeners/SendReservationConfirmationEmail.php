<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Application\Mail\ReservationCreatedMail;
use App\Events\ReservationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendReservationConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int    $tries = 3;
    public int    $backoff = 30;

    public function handle(ReservationCreated $event): void
    {
        $reservation = $event->reservation->loadMissing('space');

        Mail::to($reservation->user_email, $reservation->user_name)
            ->send(new ReservationCreatedMail($reservation));
    }

    public function failed(ReservationCreated $event, \Throwable $exception): void
    {
        Log::error('Error enviando email de creación de reserva', [
            'reservation_slug' => $event->reservation->slug,
            'user_email'       => $event->reservation->user_email,
            'error'            => $exception->getMessage(),
        ]);
    }
}
