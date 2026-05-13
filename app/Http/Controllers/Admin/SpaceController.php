<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\UseCases\Admin\SpaceUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpaceRequest;
use App\Http\Requests\Admin\UpdateSpaceRequest;
use App\Domain\Models\Space;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class SpaceController extends Controller
{
    public function __construct(
        private readonly SpaceUseCase $spaceUseCase,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Spaces/Index', [
            'spaces' => $this->spaceUseCase->listSpaces(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Spaces/Create');
    }

    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        $this->spaceUseCase->createSpace($request->validated());

        return redirect()
            ->route('admin.spaces.index')
            ->with('success', 'Sala creada correctamente.');
    }

    public function edit(Space $space): Response
    {
        return Inertia::render('Admin/Spaces/Edit', [
            'space' => $space,
        ]);
    }

    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        $this->spaceUseCase->updateSpace($space, $request->validated());

        return redirect()
            ->route('admin.spaces.index')
            ->with('success', "Sala \"{$space->name}\" actualizada correctamente.");
    }

    public function destroy(Space $space): RedirectResponse
    {
        try {
            $this->spaceUseCase->deleteSpace($space);
            return redirect()
                ->route('admin.spaces.index')
                ->with('success', "Sala \"{$space->name}\" eliminada correctamente.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['space' => $e->getMessage()]);
        }
    }
}
