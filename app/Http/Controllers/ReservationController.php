<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\UseCases\ReservationUseCase;
use App\Application\UseCases\SpaceUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationUseCase $reservationUseCase,
        private readonly SpaceUseCase $spaceUseCase,
    ) {}

    public function create(Request $request): Response
    {
        $spaceSlug = $request->query('space');
        $startTime = $request->query('start');
        $duration = (int) $request->query('duration', 60);

        $space = $spaceSlug
            ? $this->spaceUseCase->findSpaceBySlug($spaceSlug) ?? abort(404)
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

        try {
            $reservation = $this->reservationUseCase->create($data);
            return redirect()->route('reservations.show', $reservation->slug);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['space_id' => $e->getMessage()])->withInput();
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['start_time' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $slug): Response
    {
        $data = $this->reservationUseCase->getReservationWithHistory($slug);

        return Inertia::render('Reservations/Show', $data);
    }

    public function history(Request $request): Response
    {
        $email = $request->query('email');
        $reservations = $email ? $this->reservationUseCase->getHistory($email) : [];

        return Inertia::render('Reservations/History', [
            'reservations' => $reservations,
            'email'        => $email ?? '',
        ]);
    }
}
