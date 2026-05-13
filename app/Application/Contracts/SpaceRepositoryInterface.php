<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Models\Space;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SpaceRepositoryInterface
{
    public function findById(int $id): ?Space;

    public function findBySlug(string $slug): ?Space;

    public function findOrFailBySlug(string $slug): Space;

    public function findOrFail(int $id): Space;

    public function getActiveSpaces(): Collection;

    public function getActiveSpacesList(): Collection;

    public function paginateActiveSpaces(): LengthAwarePaginator;

    public function getSpacesWithReservationsCount(): LengthAwarePaginator;

    public function create(array $data): Space;

    public function update(Space $space, array $data): bool;

    public function delete(Space $space): ?bool;

    public function slugExists(string $slug, ?int $excludeId = null): bool;

    public function loadRelations(Space $space, array $relations): Space;
}
