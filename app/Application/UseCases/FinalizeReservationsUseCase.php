<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Contracts\ReservationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class FinalizeReservationsUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
    ) {}

    public function getExpiredReservations(): Collection
    {
        return $this->reservationRepo->getFinalizableReservations(Carbon::now()->toDateTimeString());
    }

    public function finalizeExpired(): int
    {
        return $this->reservationRepo->finalizePastConfirmed(Carbon::now()->toDateTimeString());
    }
}
