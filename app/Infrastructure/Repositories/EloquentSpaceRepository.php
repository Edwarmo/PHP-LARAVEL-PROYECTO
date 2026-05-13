<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Space;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentSpaceRepository implements SpaceRepositoryInterface
{
    public function findById(int $id): ?Space
    {
        return Space::find($id);
    }

    public function findBySlug(string $slug): ?Space
    {
        return Space::where('slug', $slug)->first();
    }

    public function findOrFailBySlug(string $slug): Space
    {
        return Space::where('slug', $slug)->firstOrFail();
    }

    public function findOrFail(int $id): Space
    {
        return Space::findOrFail($id);
    }

    public function getActiveSpaces(): Collection
    {
        return Space::active()->orderBy('name')->get();
    }

    public function getActiveSpacesList(): Collection
    {
        return Space::active()->orderBy('name')->get(['id', 'name']);
    }

    public function paginateActiveSpaces(): LengthAwarePaginator
    {
        return Space::active()->orderBy('name')->paginate(15)->withQueryString();
    }

    public function getSpacesWithReservationsCount(): LengthAwarePaginator
    {
        return Space::withCount('reservations')->orderBy('name')->paginate(15)->withQueryString();
    }

    public function create(array $data): Space
    {
        return Space::create($data);
    }

    public function update(Space $space, array $data): bool
    {
        return $space->update($data);
    }

    public function delete(Space $space): ?bool
    {
        return $space->delete();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        return Space::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function loadRelations(Space $space, array $relations): Space
    {
        return $space->load($relations);
    }
}
