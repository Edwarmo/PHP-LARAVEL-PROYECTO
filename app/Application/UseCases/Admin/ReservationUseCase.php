<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ReservationUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly SpaceRepositoryInterface $spaceRepo,
    ) {}

    public function getFilteredReservationsData(?string $status, ?int $spaceId, ?string $from, ?string $to): array
    {
        return [
            'reservations' => $this->reservationRepo
                ->getFilteredReservations($status, $spaceId, $from, $to)
                ->through(fn (Reservation $r) => [
                    'slug'       => $r->slug,
                    'user_name'  => $r->user_name,
                    'user_email' => $r->user_email,
                    'space_name' => $r->space->name,
                    'start_time' => $r->start_time->toIso8601String(),
                    'status'     => $r->status,
                ]),
            'spaces'   => $this->spaceRepo->getActiveSpacesList(),
            'statuses' => [
                Reservation::STATUS_PENDIENTE,
                Reservation::STATUS_CONFIRMADA,
                Reservation::STATUS_RECHAZADA,
                Reservation::STATUS_CANCELADA,
                Reservation::STATUS_FINALIZADA,
            ],
        ];
    }

    public function getReservationDetail(string $slug): Reservation
    {
        return $this->reservationRepo->getReservationWithSpace($slug);
    }

    public function updateStatus(string $slug, array $data): void
    {
        $reservation = $this->reservationRepo->findOrFailBySlug($slug);

        $previousStatus = $reservation->status;
        $this->reservationRepo->update($reservation, $data);

        if ($previousStatus !== $data['status']) {
            event(new \App\Events\ReservationStatusChanged($reservation, $previousStatus));
        }
    }

    public function deleteReservation(string $slug): void
    {
        $reservation = $this->reservationRepo->findOrFailBySlug($slug);
        $this->reservationRepo->delete($reservation);
    }
}
