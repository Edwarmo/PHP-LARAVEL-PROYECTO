<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin\CalendarController
 *
 * Vista semanal de reservas filtradas por espacio.
 * Ruta: GET /admin/calendar?space_id=&week=YYYY-MM-DD
 */
final class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        // ── Parámetros ────────────────────────────────────────────
        $spaceId   = $request->query('space_id') ? (int) $request->query('space_id') : null;
        $weekParam = $request->query('week');       // "YYYY-MM-DD" del lunes de la semana

        // Calcular lunes de la semana actual o la indicada
        $weekStart = $weekParam
            ? CarbonImmutable::parse($weekParam)->startOfWeek()
            : CarbonImmutable::now()->startOfWeek();

        $weekEnd = $weekStart->endOfWeek();         // domingo 23:59:59

        // ── Salas activas para el filtro ─────────────────────────
        $spaces = Space::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type', 'capacity']);

        // ── Reservas de la semana ─────────────────────────────────
        $query = Reservation::with('space')
            ->whereIn('status', [
                Reservation::STATUS_PENDIENTE,
                Reservation::STATUS_CONFIRMADA,
            ])
            ->where('start_time', '<', $weekEnd->toDateTimeString())
            ->where('end_time', '>', $weekStart->toDateTimeString());

        if ($spaceId) {
            $query->where('space_id', $spaceId);
        }

        $reservations = $query
            ->orderBy('start_time')
            ->get()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'space_id'   => $r->space_id,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
                // Posición para el calendario frontend
                'day_index'  => $r->start_time->dayOfWeekIso - 1, // 0=Lunes, 6=Domingo
            ]);

        // ── Días de la semana para el encabezado ─────────────────
        $weekDays = collect(range(0, 6))->map(fn (int $i) => [
            'date'  => $weekStart->addDays($i)->toDateString(),
            'label' => $weekStart->addDays($i)->translatedFormat('D d/m'),
        ]);

        return Inertia::render('Admin/Calendar', [
            'reservations'     => $reservations,
            'spaces'           => $spaces,
            'weekDays'         => $weekDays,
            'weekStart'        => $weekStart->toDateString(),
            'weekEnd'          => $weekEnd->toDateString(),
            'prevWeek'         => $weekStart->subWeek()->toDateString(),
            'nextWeek'         => $weekStart->addWeek()->toDateString(),
            'selectedSpaceId'  => $spaceId,
        ]);
    }
}
