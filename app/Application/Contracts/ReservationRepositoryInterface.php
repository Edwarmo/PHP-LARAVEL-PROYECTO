<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;

    public function findBySlug(string $slug): ?Reservation;

    public function findOrFailBySlug(string $slug): Reservation;

    public function create(array $data): Reservation;

    public function update(Reservation $reservation, array $data): bool;

    public function delete(Reservation $reservation): ?bool;

    public function getFilteredReservations(?string $status, ?int $spaceId, ?string $from, ?string $to): LengthAwarePaginator;

    public function getDashboardCounts(): array;

    public function getPendingReservations(int $limit = 10): Collection;

    public function getTodayReservations(): Collection;

    public function getRecentReservations(int $limit = 20): Collection;

    public function getCalendarReservations(string $weekStart, string $weekEnd, ?int $spaceId = null): Collection;

    public function getActiveForSpaceAndDate(int $spaceId, string $startDate, string $endDate): Collection;

    public function overlappingExists(int $spaceId, string $start, string $end): bool;

    public function getActiveForSpaceBetween(int $spaceId, string $dayStart, string $dayEnd): Collection;

    public function getReservationsByEmail(string $email, ?string $excludeSlug = null, int $limit = 10): Collection;

    public function getFinalizableReservations(string $before): Collection;

    public function finalizePastConfirmed(string $before): int;

    public function getReservationWithSpace(string $slug): Reservation;
}
