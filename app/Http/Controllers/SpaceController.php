<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\UseCases\SpaceUseCase;
use App\Domain\Models\Space;
use Inertia\Inertia;
use Inertia\Response;

final class SpaceController extends Controller
{
    public function __construct(
        private readonly SpaceUseCase $spaceUseCase,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Spaces/Index', [
            'spaces' => $this->spaceUseCase->listSpaces(),
        ]);
    }

    public function show(Space $space): Response
    {
        $slotMinutes = $this->spaceUseCase->resolveSlotMinutes();
        $data = $this->spaceUseCase->getSpaceDetail($space, $slotMinutes);

        return Inertia::render('Spaces/Show', $data);
    }
}
