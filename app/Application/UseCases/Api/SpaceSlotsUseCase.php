<?php

declare(strict_types=1);

namespace App\Application\UseCases\Api;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Space;
use Carbon\Carbon;

final class SpaceSlotsUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly SpaceRepositoryInterface $spaceRepo,
    ) {}

    public function getSlots(Space $space, string $date, int $slotMinutes): array
    {
        $this->spaceRepo->loadRelations($space, ['availabilities', 'blockedSlots']);

        $carbon    = Carbon::parse($date);
        $dayOfWeek = (int) $carbon->format('N') % 7;

        $availability = $space->availabilities->firstWhere('day_of_week', $dayOfWeek);

        if (! $availability) {
            return [];
        }

        return $this->buildSlots($space, $carbon, $availability->start_time, $availability->end_time, $slotMinutes);
    }

    private function buildSlots(Space $space, Carbon $date, string $startTime, string $endTime, int $slotMinutes): array
    {
        $start = Carbon::parse($date->toDateString() . ' ' . $startTime);
        $end   = Carbon::parse($date->toDateString() . ' ' . $endTime);

        $reservations = $this->reservationRepo->getActiveForSpaceAndDate(
            $space->id,
            $date->toDateString(),
            $date->toDateString()
        );

        $blockedSlots = $space->blockedSlots->filter(
            fn ($b) => $b->start_time->toDateString() === $date->toDateString()
        );

        $slots  = [];
        $cursor = $start->copy();

        while ($cursor->copy()->addMinutes($slotMinutes)->lte($end)) {
            $slotStart = $cursor->copy();
            $slotEnd   = $cursor->copy()->addMinutes($slotMinutes);

            $occupied = $reservations->contains(
                fn ($r) => $r->start_time->lt($slotEnd) && $r->end_time->gt($slotStart)
            ) || $blockedSlots->contains(
                fn ($b) => $b->start_time->lt($slotEnd) && $b->end_time->gt($slotStart)
            );

            $slots[] = [
                'label'     => $slotStart->format('H:i') . ' – ' . $slotEnd->format('H:i'),
                'available' => ! $occupied,
            ];

            $cursor->addMinutes($slotMinutes);
        }

        return $slots;
    }
}
