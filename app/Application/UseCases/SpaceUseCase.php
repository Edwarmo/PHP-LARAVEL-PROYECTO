<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SpaceUseCase
{
    public function __construct(
        private readonly SpaceRepositoryInterface $spaceRepo,
        private readonly ReservationRepositoryInterface $reservationRepo,
    ) {}

    public function listSpaces(): LengthAwarePaginator
    {
        return $this->spaceRepo->paginateActiveSpaces();
    }

    public function getSpaceDetail(Space $space, int $slotMinutes): array
    {
        if (! (bool) $space->is_active) {
            abort(404);
        }

        $this->spaceRepo->loadRelations($space, ['availabilities', 'blockedSlots']);

        $nextAvailableDays = $this->getNextAvailableDays($space, $slotMinutes);

        return [
            'space'             => $space,
            'nextAvailableDays' => $nextAvailableDays,
            'slotMinutes'       => $slotMinutes,
        ];
    }

    public function findSpaceBySlug(string $slug): ?Space
    {
        return $this->spaceRepo->findBySlug($slug);
    }

    public function findSpaceWithAvailabilitiesAndBlockedSlots(int $id): Space
    {
        return $this->spaceRepo->loadRelations(
            $this->spaceRepo->findOrFail($id),
            ['availabilities', 'blockedSlots']
        );
    }

    public function resolveSlotMinutes(): int
    {
        return (int) config('reservation.slot_minutes', env('RESERVATION_SLOT_MINUTES', 60));
    }

    private function getNextAvailableDays(Space $space, int $slotMinutes): array
    {
        $days = [];
        $today = Carbon::today();

        for ($i = 0; $i < 14; $i++) {
            $date      = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->format('N') % 7;

            $availability = $space->availabilities->firstWhere('day_of_week', $dayOfWeek);

            if (! $availability) {
                continue;
            }

            $slots = $this->buildSlots($space, $date, $availability->start_time, $availability->end_time, $slotMinutes);

            $availableCount = collect($slots)->where('available', true)->count();

            if ($availableCount === 0) {
                continue;
            }

            $days[] = [
                'date'                  => $date->toDateString(),
                'day_name'              => $date->locale('es')->isoFormat('dddd'),
                'available_slots_count' => $availableCount,
            ];

            if (count($days) >= 7) {
                break;
            }
        }

        return $days;
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
