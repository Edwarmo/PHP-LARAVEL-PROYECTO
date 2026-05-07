<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin\DashboardController
 *
 * Métricas generales del sistema de reservas.
 * Ruta: GET /admin  (middleware: auth, verified)
 */
final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $now   = Carbon::now();
        $today = Carbon::today();

        // ── Métricas de reservas ──────────────────────────────────
        $metrics = [
            'pendientes'  => Reservation::where('status', Reservation::STATUS_PENDIENTE)->count(),
            'confirmadas' => Reservation::where('status', Reservation::STATUS_CONFIRMADA)->count(),
            'hoy'         => Reservation::where('status', Reservation::STATUS_CONFIRMADA)
                                ->whereDate('start_time', $today)
                                ->count(),
            'esta_semana' => Reservation::where('status', Reservation::STATUS_CONFIRMADA)
                                ->whereBetween('start_time', [
                                    $today->startOfWeek(),
                                    $today->copy()->endOfWeek(),
                                ])
                                ->count(),
            'total_salas_activas' => Space::where('is_active', 'true')->count(),
        ];

        // ── Próximas reservas confirmadas (siguiente 48 h) ────────
        $proximasReservas = Reservation::with('space')
            ->where('status', Reservation::STATUS_CONFIRMADA)
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $now->copy()->addHours(48))
            ->orderBy('start_time')
            ->limit(10)
            ->get()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'status'     => $r->status,
            ]);

        // ── Reservas pendientes de acción ─────────────────────────
        $pendientes = Reservation::with('space')
            ->where('status', Reservation::STATUS_PENDIENTE)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn (Reservation $r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'user_email' => $r->user_email,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'end_time'   => $r->end_time->toIso8601String(),
                'created_at' => $r->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'metrics'          => $metrics,
            'proximasReservas' => $proximasReservas,
            'pendientes'       => $pendientes,
        ]);
    }
}
