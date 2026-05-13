<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Domain\Models\Reservation;

final class DashboardUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
    ) {}

    public function getDashboardData(): array
    {
        $counts = $this->reservationRepo->getDashboardCounts();

        $pendientes = $this->reservationRepo
            ->getPendingReservations()
            ->map(fn ($r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'status'     => $r->status,
            ]);

        return [
            'metrics'          => $counts,
            'pendientes'       => $pendientes,
            'proximasReservas' => [],
        ];
    }
}
