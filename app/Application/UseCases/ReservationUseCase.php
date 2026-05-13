<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Reservation;
use App\Infrastructure\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

final class ReservationUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly SpaceRepositoryInterface $spaceRepo,
        private readonly AvailabilityService $availabilityService,
    ) {}

    public function create(array $data): Reservation
    {
        $space = $this->spaceRepo->loadRelations(
            $this->spaceRepo->findOrFail($data['space_id']),
            ['availabilities', 'blockedSlots']
        );

        if (! (bool) $space->is_active) {
            throw new \RuntimeException('Esta sala no está activa.');
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($data['duration']);

        if ($startTime->isPast()) {
            throw new \InvalidArgumentException('No se puede reservar en una fecha pasada.');
        }

        if (! $this->availabilityService->isSlotAvailable($space, $startTime, $endTime)) {
            throw new \InvalidArgumentException('El horario seleccionado no está disponible.');
        }

        $reservation = $this->reservationRepo->create([
            'space_id'   => $data['space_id'],
            'user_name'  => $data['user_name'],
            'user_email' => $data['user_email'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'notes'      => $data['notes'] ?? null,
            'status'     => Reservation::STATUS_PENDIENTE,
        ]);

        event(new \App\Events\ReservationCreated($reservation));

        return $reservation;
    }

    public function getReservationWithHistory(string $slug): array
    {
        $reservation = $this->reservationRepo->getReservationWithSpace($slug);

        $userReservations = $this->reservationRepo
            ->getReservationsByEmail($reservation->user_email, $slug)
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
            ]);

        return [
            'reservation'      => $reservation,
            'userReservations' => $userReservations,
        ];
    }

    public function getHistory(?string $email): array
    {
        if (! $email) {
            return [];
        }

        return $this->reservationRepo
            ->getReservationsByEmail($email, null, 1000)
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'space_name' => $r->space->name,
                'user_name'  => $r->user_name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
            ])
            ->all();
    }
}
