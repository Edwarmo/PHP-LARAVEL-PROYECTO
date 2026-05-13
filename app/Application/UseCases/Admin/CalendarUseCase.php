<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Reservation;
use Carbon\CarbonImmutable;

final class CalendarUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly SpaceRepositoryInterface $spaceRepo,
    ) {}

    public function getCalendarData(?int $spaceId, ?string $weekParam): array
    {
        $weekStart = $weekParam
            ? CarbonImmutable::parse($weekParam)->startOfWeek()
            : CarbonImmutable::now()->startOfWeek();

        $weekEnd = $weekStart->endOfWeek();

        $spaces = $this->spaceRepo->getActiveSpaces();

        $reservations = $this->reservationRepo
            ->getCalendarReservations(
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString(),
                $spaceId
            )
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'space_id'   => $r->space_id,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
                'day_index'  => $r->start_time->dayOfWeekIso - 1,
            ]);

        $weekDays = collect(range(0, 6))->map(fn (int $i) => [
            'date'  => $weekStart->addDays($i)->toDateString(),
            'label' => $weekStart->addDays($i)->translatedFormat('D d/m'),
        ]);

        return [
            'reservations'     => $reservations,
            'spaces'           => $spaces,
            'weekDays'         => $weekDays,
            'weekStart'        => $weekStart->toDateString(),
            'weekEnd'          => $weekEnd->toDateString(),
            'prevWeek'         => $weekStart->subWeek()->toDateString(),
            'nextWeek'         => $weekStart->addWeek()->toDateString(),
            'selectedSpaceId'  => $spaceId,
        ];
    }
}
