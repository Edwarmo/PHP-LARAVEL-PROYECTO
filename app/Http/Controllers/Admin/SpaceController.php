<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpaceRequest;
use App\Http\Requests\Admin\UpdateSpaceRequest;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin\SpaceController — CRUD de Salas
 *
 * Rutas:
 *   GET    /admin/spaces            → index
 *   GET    /admin/spaces/create     → create
 *   POST   /admin/spaces            → store
 *   GET    /admin/spaces/{space}    → show   (redirige a edit)
 *   GET    /admin/spaces/{space}/edit → edit
 *   PUT    /admin/spaces/{space}    → update
 *   DELETE /admin/spaces/{space}    → destroy
 */
final class SpaceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  GET /admin/spaces
    // ─────────────────────────────────────────────────────────────

    public function index(): Response
    {
        $spaces = Space::withCount('reservations')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /admin/spaces/create
    // ─────────────────────────────────────────────────────────────

    public function create(): Response
    {
        return Inertia::render('Admin/Spaces/Create');
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /admin/spaces
    // ─────────────────────────────────────────────────────────────

    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        $data         = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        Space::create($data);

        return redirect()
            ->route('admin.spaces.index')
            ->with('success', 'Sala creada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /admin/spaces/{space}/edit
    // ─────────────────────────────────────────────────────────────

    public function edit(Space $space): Response
    {
        return Inertia::render('Admin/Spaces/Edit', [
            'space' => $space,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUT /admin/spaces/{space}
    // ─────────────────────────────────────────────────────────────

    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        $data = $request->validated();

        // Regenerar slug solo si el nombre cambió
        if ($data['name'] !== $space->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $space->id);
        }

        $space->update($data);

        return redirect()
            ->route('admin.spaces.index')
            ->with('success', "Sala \"{$space->name}\" actualizada correctamente.");
    }

    // ─────────────────────────────────────────────────────────────
    //  DELETE /admin/spaces/{space}
    // ─────────────────────────────────────────────────────────────

    public function destroy(Space $space): RedirectResponse
    {
        // Protección: no eliminar salas con reservas confirmadas o pendientes
        $activeCount = $space->reservations()
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->count();

        if ($activeCount > 0) {
            return back()->withErrors([
                'space' => "No se puede eliminar la sala \"{$space->name}\" porque tiene {$activeCount} reserva(s) activa(s).",
            ]);
        }

        $name = $space->name;
        $space->delete();

        return redirect()
            ->route('admin.spaces.index')
            ->with('success', "Sala \"{$name}\" eliminada correctamente.");
    }

    // ─────────────────────────────────────────────────────────────
    //  Helper — Slug único
    // ─────────────────────────────────────────────────────────────

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (
            Space::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
