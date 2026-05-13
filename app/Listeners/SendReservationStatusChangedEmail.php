<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Application\Mail\ReservationStatusChangedMail;
use App\Events\ReservationStatusChanged;
use App\Domain\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendReservationStatusChangedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int    $tries = 3;
    public int    $backoff = 30;

    public function handle(ReservationStatusChanged $event): void
    {
        $reservation = $event->reservation->loadMissing('space');

        $notifiableStatuses = [
            Reservation::STATUS_CONFIRMADA,
            Reservation::STATUS_RECHAZADA,
            Reservation::STATUS_CANCELADA,
        ];

        if (! in_array($reservation->status, $notifiableStatuses, true)) {
            return;
        }

        Mail::to($reservation->user_email, $reservation->user_name)
            ->send(new ReservationStatusChangedMail($reservation, $event->previousStatus));
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
