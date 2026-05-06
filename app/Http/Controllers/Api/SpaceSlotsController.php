<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SpaceSlotsController extends Controller
{
    public function __invoke(Request $request, Space $space): JsonResponse
    {
        $date        = $request->query('date', now()->toDateString());
        $slotMinutes = (int) env('RESERVATION_SLOT_MINUTES', 60);

        $space->load(['availabilities', 'blockedSlots']);

        $carbon    = Carbon::parse($date);
        $dayOfWeek = (int) $carbon->format('N') % 7;

        $availability = $space->availabilities->firstWhere('day_of_week', $dayOfWeek);

        if (! $availability) {
            return response()->json(['slots' => []]);
        }

        $slots = $this->buildSlots($space, $carbon, $availability->start_time, $availability->end_time, $slotMinutes);

        return response()->json(['slots' => $slots]);
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
