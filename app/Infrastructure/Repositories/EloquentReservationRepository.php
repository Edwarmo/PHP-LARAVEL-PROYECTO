<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Application\Contracts\ReservationRepositoryInterface;
use App\Domain\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation
    {
        return Reservation::find($id);
    }

    public function findBySlug(string $slug): ?Reservation
    {
        return Reservation::where('slug', $slug)->first();
    }

    public function findOrFailBySlug(string $slug): Reservation
    {
        return Reservation::where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function update(Reservation $reservation, array $data): bool
    {
        return $reservation->update($data);
    }

    public function delete(Reservation $reservation): ?bool
    {
        return $reservation->delete();
    }

    public function getFilteredReservations(?string $status, ?int $spaceId, ?string $from, ?string $to): LengthAwarePaginator
    {
        $query = Reservation::with('space')->latest('start_time');

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($spaceId !== null) {
            $query->where('space_id', $spaceId);
        }

        if ($from !== null) {
            $query->where('start_time', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to !== null) {
            $query->where('start_time', '<=', Carbon::parse($to)->endOfDay());
        }

        return $query->paginate(20)->withQueryString();
    }

    public function getDashboardCounts(): array
    {
        return [
            'pendientes'  => Reservation::pendiente()->count(),
            'confirmadas' => Reservation::confirmada()->count(),
            'hoy'         => Reservation::whereDate('start_time', today())->count(),
            'esta_semana' => Reservation::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    public function getPendingReservations(int $limit = 10): Collection
    {
        return Reservation::with('space')
            ->pendiente()
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function getCalendarReservations(string $weekStart, string $weekEnd, ?int $spaceId = null): Collection
    {
        $query = Reservation::with('space')
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->where('start_time', '<', $weekEnd)
            ->where('end_time', '>', $weekStart)
            ->orderBy('start_time');

        if ($spaceId !== null) {
            $query->where('space_id', $spaceId);
        }

        return $query->get();
    }

    public function getActiveForSpaceAndDate(int $spaceId, string $startDate, string $endDate): Collection
    {
        return Reservation::where('space_id', $spaceId)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->whereDate('start_time', $startDate)
            ->get();
    }

    public function overlappingExists(int $spaceId, string $start, string $end): bool
    {
        return Reservation::where('space_id', $spaceId)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }

    public function getActiveForSpaceBetween(int $spaceId, string $dayStart, string $dayEnd): Collection
    {
        return Reservation::where('space_id', $spaceId)
            ->whereIn('status', [Reservation::STATUS_PENDIENTE, Reservation::STATUS_CONFIRMADA])
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->get();
    }

    public function getReservationsByEmail(string $email, ?string $excludeSlug = null, int $limit = 10): Collection
    {
        $query = Reservation::with('space')
            ->where('user_email', $email)
            ->orderBy('start_time', 'desc');

        if ($excludeSlug !== null) {
            $query->where('slug', '!=', $excludeSlug);
        }

        return $query->limit($limit)->get();
    }

    public function getFinalizableReservations(string $before): Collection
    {
        return Reservation::with('space')
            ->where('status', Reservation::STATUS_CONFIRMADA)
            ->where('end_time', '<', $before)
            ->get();
    }

    public function finalizePastConfirmed(string $before): int
    {
        return Reservation::where('status', Reservation::STATUS_CONFIRMADA)
            ->where('end_time', '<', $before)
            ->update(['status' => Reservation::STATUS_FINALIZADA]);
    }

    public function getReservationWithSpace(string $slug): Reservation
    {
        return Reservation::with('space')->where('slug', $slug)->firstOrFail();
    }
}
