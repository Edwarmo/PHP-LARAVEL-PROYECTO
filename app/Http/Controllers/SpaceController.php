<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final class SpaceController extends Controller
{
    public function index(): Response
    {
        $spaces = Space::active()->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    public function show(Space $space): Response
    {
        if (! (bool) $space->is_active) {
            abort(404);
        }

        $slotMinutes = (int) config('reservation.slot_minutes', env('RESERVATION_SLOT_MINUTES', 60));

        $space->load(['availabilities', 'blockedSlots']);

        $nextAvailableDays = $this->getNextAvailableDays($space, $slotMinutes);

        return Inertia::render('Spaces/Show', [
            'space'             => $space,
            'nextAvailableDays' => $nextAvailableDays,
            'slotMinutes'       => $slotMinutes,
        ]);
    }

    private function getNextAvailableDays(Space $space, int $slotMinutes): array
    {
        $days = [];
        $today = Carbon::today();

        for ($i = 0; $i < 14; $i++) {
            $date      = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->format('N') % 7; // Carbon: 1=Mon..7=Sun → 0=Sun..6=Sat

            $availability = $space->availabilities
                ->firstWhere('day_of_week', $dayOfWeek);

            if (! $availability) {
                continue;
            }

            $slots = $this->buildSlots($space, $date, $availability->start_time, $availability->end_time, $slotMinutes);

            $availableCount = collect($slots)->where('available', true)->count();

            if ($availableCount === 0) {
                continue;
            }

            $days[] = [
                'date'                 => $date->toDateString(),
                'day_name'             => $date->locale('es')->isoFormat('dddd'),
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

        $reservations = Reservation::where('space_id', $space->id)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->whereDate('start_time', $date->toDateString())
            ->get();

        $blockedSlots = $space->blockedSlots->filter(
            fn ($b) => $b->start_time->toDateString() === $date->toDateString()
        );

        $slots = [];
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
