<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\ReservationCreated;
use App\Models\Reservation;
use App\Models\Space;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ReservationController extends Controller
{
    public function create(Request $request): Response
    {
        $spaceSlug = $request->query('space');
        $startTime = $request->query('start');
        $duration = (int) $request->query('duration', 60);

        $space = $spaceSlug
            ? Space::where('slug', $spaceSlug)->firstOrFail()
            : null;

        return Inertia::render('Reservations/Create', [
            'space'    => $space,
            'start'    => $startTime,
            'duration' => $duration,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'space_id'   => ['required', 'integer', 'exists:spaces,id'],
            'user_name'  => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255'],
            'start_time' => ['required', 'date'],
            'duration'   => ['required', 'integer', 'min:30', 'max:480'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $space = Space::with(['availabilities', 'blockedSlots'])->findOrFail($data['space_id']);

        if (! (bool) $space->is_active) {
            return back()->withErrors(['space_id' => 'Esta sala no está activa.'])->withInput();
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($data['duration']);

        if ($startTime->isPast()) {
            return back()->withErrors(['start_time' => 'No se puede reservar en una fecha pasada.'])->withInput();
        }

        $availService = AvailabilityService::make();
        if (! $availService->isSlotAvailable($space, $startTime, $endTime)) {
            return back()->withErrors(['start_time' => 'El horario seleccionado no está disponible.'])->withInput();
        }

        $reservation = Reservation::create([
            'space_id'   => $data['space_id'],
            'user_name'  => $data['user_name'],
            'user_email' => $data['user_email'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'notes'      => $data['notes'] ?? null,
            'status'     => Reservation::STATUS_PENDIENTE,
        ]);

        event(new ReservationCreated($reservation));

        return redirect()->route('reservations.show', $reservation->slug);
    }

    public function show(string $slug): Response
    {
        $reservation = Reservation::with('space')
            ->where('slug', $slug)
            ->firstOrFail();

        // Obtener otras reservas del mismo usuario (historial)
        $userReservations = Reservation::with('space')
            ->where('user_email', $reservation->user_email)
            ->where('slug', '!=', $slug)
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
            ]);

        return Inertia::render('Reservations/Show', [
            'reservation'      => $reservation,
            'userReservations' => $userReservations,
        ]);
    }

    public function history(Request $request): Response
    {
        // Si no hay búsqueda, usamos el email del usuario logueado por defecto
        $email = $request->query('email', auth()->user()?->email);
        $reservations = [];

        if ($email) {
            $reservations = Reservation::with('space')
                ->where('user_email', $email)
                ->orderBy('start_time', 'desc')
                ->get()
                ->map(fn (Reservation $r) => [
                    'slug'       => $r->slug,
                    'space_name' => $r->space->name,
                    'user_name'  => $r->user_name,
                    'start_time' => $r->start_time->toIso8601String(),
                    'end_time'   => $r->end_time->toIso8601String(),
                    'status'     => $r->status,
                ]);
        }

        return Inertia::render('Reservations/History', [
            'reservations' => $reservations,
            'email'        => $email,
        ]);
    }
}
