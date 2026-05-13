<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\UseCases\Admin\ReservationUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationUseCase $reservationUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->reservationUseCase->getFilteredReservationsData(
            $request->query('status'),
            $request->query('space_id') ? (int) $request->query('space_id') : null,
            $request->query('from'),
            $request->query('to'),
        );

        return Inertia::render('AdminReservationsIndex', [
            'reservations' => Inertia::defer(fn () => $data['reservations']),
            'spaces'       => $data['spaces'],
            'filters'      => $request->only(['status', 'space_id', 'from', 'to']),
            'statuses'     => $data['statuses'],
        ]);
    }

    public function show(string $slug): Response
    {
        return Inertia::render('AdminReservationsShow', [
            'reservation' => $this->reservationUseCase->getReservationDetail($slug),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $data = $request->validate([
            'status'     => ['required', 'string'],
            'notes'      => ['nullable', 'string', 'max:1000'],
            'user_name'  => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255'],
        ]);

        $this->reservationUseCase->updateStatus($slug, $data);

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        $this->reservationUseCase->deleteReservation($slug);

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reserva eliminada permanentemente.');
    }
}
