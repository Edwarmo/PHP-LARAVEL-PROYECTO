<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Events\ReservationStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin\ReservationController
 *
 * Gestión y cambio de estado de reservas.
 *
 * Rutas:
 *   GET  /admin/reservations              → index (con filtros)
 *   GET  /admin/reservations/{slug}       → show
 *   POST /admin/reservations/{slug}/accept → accept
 *   POST /admin/reservations/{slug}/reject → reject
 *   POST /admin/reservations/{slug}/cancel → cancel
 */
final class ReservationController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  GET /admin/reservations
    // ─────────────────────────────────────────────────────────────

    /**
     * Listado con filtros por status, space_id y fecha.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('AdminReservationsIndex', [
            'reservations' => Inertia::defer(function () use ($request) {
                $query = Reservation::with('space')->latest('start_time');

                if ($status = $request->query('status')) {
                    $query->where('status', $status);
                }

                if ($spaceId = $request->query('space_id')) {
                    $query->where('space_id', (int) $spaceId);
                }

                if ($from = $request->query('from')) {
                    $query->where('start_time', '>=', \Carbon\Carbon::parse($from)->startOfDay());
                }

                if ($to = $request->query('to')) {
                    $query->where('start_time', '<=', \Carbon\Carbon::parse($to)->endOfDay());
                }

                return $query->paginate(20)->withQueryString()->through(fn (Reservation $r) => [
                    'slug'       => $r->slug,
                    'user_name'  => $r->user_name,
                    'user_email' => $r->user_email,
                    'space_name' => $r->space->name,
                    'start_time' => $r->start_time->toIso8601String(),
                    'status'     => $r->status,
                ]);
            }),
            'spaces'  => Space::active()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'space_id', 'from', 'to']),
            'statuses' => [
                Reservation::STATUS_PENDIENTE,
                Reservation::STATUS_CONFIRMADA,
                Reservation::STATUS_RECHAZADA,
                Reservation::STATUS_CANCELADA,
                Reservation::STATUS_FINALIZADA,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /admin/reservations/{slug}
    // ─────────────────────────────────────────────────────────────

    public function show(string $slug): Response
    {
        $reservation = Reservation::with('space')
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('AdminReservationsShow', [
            'reservation' => $reservation,
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $reservation = Reservation::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'status'     => ['required', 'string'],
            'notes'      => ['nullable', 'string', 'max:1000'],
            'user_name'  => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255'],
        ]);

        $previousStatus = $reservation->status;
        $reservation->update($data);

        if ($previousStatus !== $data['status']) {
            event(new ReservationStatusChanged($reservation, $previousStatus));
        }

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $reservation = Reservation::where('slug', $slug)->firstOrFail();
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reserva eliminada permanentemente.');
    }
}
