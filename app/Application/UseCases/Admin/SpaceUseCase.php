<?php

declare(strict_types=1);

namespace App\Application\UseCases\Admin;

use App\Application\Contracts\SpaceRepositoryInterface;
use App\Domain\Models\Space;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class SpaceUseCase
{
    public function __construct(
        private readonly SpaceRepositoryInterface $spaceRepo,
    ) {}

    public function listSpaces(): LengthAwarePaginator
    {
        return $this->spaceRepo->getSpacesWithReservationsCount();
    }

    public function createSpace(array $data): Space
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        return $this->spaceRepo->create($data);
    }

    public function updateSpace(Space $space, array $data): void
    {
        if ($data['name'] !== $space->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $space->id);
        }
        $this->spaceRepo->update($space, $data);
    }

    public function deleteSpace(Space $space): void
    {
        $activeCount = $space->reservations()
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->count();

        if ($activeCount > 0) {
            throw new \RuntimeException(
                "No se puede eliminar la sala \"{$space->name}\" porque tiene {$activeCount} reserva(s) activa(s)."
            );
        }

        $this->spaceRepo->delete($space);
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while ($this->spaceRepo->slugExists($slug, $excludeId)) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
