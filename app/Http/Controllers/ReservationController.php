<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
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

        $startTime = \Carbon\Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($data['duration']);

        $reservation = Reservation::create([
            'space_id'   => $data['space_id'],
            'user_name'  => $data['user_name'],
            'user_email' => $data['user_email'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'notes'      => $data['notes'] ?? null,
            'status'     => Reservation::STATUS_PENDIENTE,
        ]);

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
