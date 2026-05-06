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
        $query = Reservation::with('space')->latest('start_time');

        // Filtro: estado
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filtro: sala
        if ($spaceId = $request->query('space_id')) {
            $query->where('space_id', (int) $spaceId);
        }

        // Filtro: fecha exacta (Y-m-d)
        if ($date = $request->query('date')) {
            $query->whereDate('start_time', Carbon::parse($date)->toDateString());
        }

        // Filtro: rango de fechas
        if ($from = $request->query('from')) {
            $query->where('start_time', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->query('to')) {
            $query->where('start_time', '<=', Carbon::parse($to)->endOfDay());
        }

        $reservations = $query->paginate(20)->withQueryString();

        $spaces = Space::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations->through(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'user_email' => $r->user_email,
                'space_name' => $r->space->name,
                'space_slug' => $r->space->slug,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
                'notes'      => $r->notes,
                'created_at' => $r->created_at->toIso8601String(),
            ]),
            'spaces'  => $spaces,
            'filters' => $request->only(['status', 'space_id', 'date', 'from', 'to']),
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

        return Inertia::render('Admin/Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /admin/reservations/{slug}/accept
    // ─────────────────────────────────────────────────────────────

    public function accept(string $slug): RedirectResponse
    {
        $reservation = Reservation::where('slug', $slug)->firstOrFail();

        if ($reservation->status !== Reservation::STATUS_PENDIENTE) {
            return back()->withErrors([
                'status' => 'Solo se pueden confirmar reservas en estado pendiente.',
            ]);
        }

        $previousStatus = $reservation->status;
        $reservation->update(['status' => Reservation::STATUS_CONFIRMADA]);

        event(new ReservationStatusChanged($reservation, $previousStatus));

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', "Reserva de \"{$reservation->user_name}\" confirmada.");
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /admin/reservations/{slug}/reject
    // ─────────────────────────────────────────────────────────────

    public function reject(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.max' => 'El motivo no puede superar 500 caracteres.',
        ]);

        $reservation = Reservation::where('slug', $slug)->firstOrFail();

        if (! in_array($reservation->status, [
            Reservation::STATUS_PENDIENTE,
            Reservation::STATUS_CONFIRMADA,
        ])) {
            return back()->withErrors([
                'status' => 'Solo se pueden rechazar reservas pendientes o confirmadas.',
            ]);
        }

        $previousStatus = $reservation->status;
        $reservation->update([
            'status' => Reservation::STATUS_RECHAZADA,
            'notes'  => $request->input('reason')
                ? ($reservation->notes . "\n[Rechazo]: " . $request->input('reason'))
                : $reservation->notes,
        ]);

        event(new ReservationStatusChanged($reservation, $previousStatus));

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', "Reserva de \"{$reservation->user_name}\" rechazada.");
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /admin/reservations/{slug}/cancel
    // ─────────────────────────────────────────────────────────────

    public function cancel(Request $request, string $slug): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.max' => 'El motivo no puede superar 500 caracteres.',
        ]);

        $reservation = Reservation::where('slug', $slug)->firstOrFail();

        if (! in_array($reservation->status, [
            Reservation::STATUS_PENDIENTE,
            Reservation::STATUS_CONFIRMADA,
        ])) {
            return back()->withErrors([
                'status' => 'Solo se pueden cancelar reservas pendientes o confirmadas.',
            ]);
        }

        $previousStatus = $reservation->status;
        $reservation->update([
            'status' => Reservation::STATUS_CANCELADA,
            'notes'  => $request->input('reason')
                ? ($reservation->notes . "\n[Cancelación]: " . $request->input('reason'))
                : $reservation->notes,
        ]);

        event(new ReservationStatusChanged($reservation, $previousStatus));

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', "Reserva de \"{$reservation->user_name}\" cancelada.");
    }
}
