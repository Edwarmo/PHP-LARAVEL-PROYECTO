<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlockedSlot;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class AvailabilityService
{
    public static function make(): self
    {
        return new self();
    }

    public function isSlotAvailable(Space $space, Carbon $start, Carbon $end): bool
    {
        if ($start->greaterThanOrEqualTo($end)) {
            throw new \InvalidArgumentException('Start time must be before end time.');
        }

        if (! $space->relationLoaded('availabilities')) {
            $space->load('availabilities');
        }
        if (! $space->relationLoaded('blockedSlots')) {
            $space->load('blockedSlots');
        }

        $dayOfWeek = (int) $start->format('N') % 7;

        $availability = $space->availabilities
            ->firstWhere('day_of_week', $dayOfWeek);

        if (! $availability) {
            return false;
        }

        $dayStart = $start->copy()->setTimeFromTimeString($availability->start_time);
        $dayEnd   = $start->copy()->setTimeFromTimeString($availability->end_time);

        if ($start->lt($dayStart) || $end->gt($dayEnd)) {
            return false;
        }

        $hasReservation = Reservation::where('space_id', $space->id)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($hasReservation) {
            return false;
        }

        $hasBlock = BlockedSlot::where('space_id', $space->id)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($hasBlock) {
            return false;
        }

        return true;
    }

    public function getAvailableSlots(Space $space, Carbon $date): Collection
    {
        $slotMinutes = (int) config('app.reservation_slot_minutes', 60);
        $dayOfWeek   = (int) $date->format('N') % 7;

        if (! $space->relationLoaded('availabilities')) {
            $space->load('availabilities');
        }
        if (! $space->relationLoaded('blockedSlots')) {
            $space->load('blockedSlots');
        }

        $availability = $space->availabilities
            ->firstWhere('day_of_week', $dayOfWeek);

        if (! $availability) {
            return collect();
        }

        $start      = Carbon::parse($date->toDateString() . ' ' . $availability->start_time);
        $end        = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);
        $dayStart = $date->copy()->startOfDay();
        $dayEnd   = $date->copy()->endOfDay();
        $reservations = Reservation::where('space_id', $space->id)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->get();

        $blockedSlots = $space->blockedSlots->filter(
            fn (BlockedSlot $b) => $b->start_time->toDateString() === $date->toDateString()
        );

        $slots  = collect();
        $cursor = $start->copy();

        while ($cursor->copy()->addMinutes($slotMinutes)->lte($end)) {
            $slotStart = $cursor->copy();
            $slotEnd   = $cursor->copy()->addMinutes($slotMinutes);

            $occupied = $reservations->contains(
                fn (Reservation $r) => $r->start_time->lt($slotEnd) && $r->end_time->gt($slotStart)
            ) || $blockedSlots->contains(
                fn (BlockedSlot $b) => $b->start_time->lt($slotEnd) && $b->end_time->gt($slotStart)
            );

            $slots->push([
                'label'     => $slotStart->format('H:i') . ' – ' . $slotEnd->format('H:i'),
                'available' => ! $occupied,
            ]);

            $cursor->addMinutes($slotMinutes);
        }

        return $slots;
    }

    public function getNextAvailableDays(Space $space, int $count = 7): Collection
    {
        $slotMinutes = (int) config('app.reservation_slot_minutes', 60);
        $days        = collect();
        $today       = Carbon::today();

        for ($i = 0; $i < 14; $i++) {
            $date      = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->format('N') % 7;

            $availability = $space->availabilities
                ->firstWhere('day_of_week', $dayOfWeek);

            if (! $availability) {
                continue;
            }

            $slots = $this->getAvailableSlots($space, $date);
            $availableCount = $slots->where('available', true)->count();

            if ($availableCount === 0) {
                continue;
            }

            $days->push([
                'date'                  => $date->toDateString(),
                'day_name'              => $date->locale('es')->isoFormat('dddd'),
                'available_slots_count' => $availableCount,
            ]);

            if ($days->count() >= $count) {
                break;
            }
        }

        return $days;
    }
}
