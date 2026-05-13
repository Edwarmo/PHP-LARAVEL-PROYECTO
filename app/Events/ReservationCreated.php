<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReservationCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
    ) {}
}
