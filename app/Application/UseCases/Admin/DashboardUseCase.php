<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Reservation;

final class DashboardUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly SpaceRepositoryInterface $spaceRepo,
    ) {}

    public function getDashboardData(): array
    {
        $counts = $this->reservationRepo->getDashboardCounts();
        $counts['total_spaces'] = $this->spaceRepo->getActiveSpaces()->count();

        $pendientes = $this->reservationRepo
            ->getPendingReservations()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time?->toIso8601String(),
                'status'     => $r->status,
            ]);

        $hoy = $this->reservationRepo
            ->getTodayReservations()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time?->toIso8601String(),
                'status'     => $r->status,
            ]);

        $recientes = $this->reservationRepo
            ->getRecentReservations()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time?->toIso8601String(),
                'status'     => $r->status,
            ]);

        return [
            'metrics'          => $counts,
            'pendientes'       => $pendientes,
            'proximasReservas' => $hoy,
            'recientes'        => $recientes,
        ];
    }
}
