<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DashboardController
 *
 * Panel principal de administración con métricas y resumen.
 */
final class DashboardController extends Controller
{
    /**
     * Muestra el resumen administrativo.
     */
    public function __invoke(Request $request): Response
    {
        $metrics = [
            'pendientes'  => Reservation::pendiente()->count(),
            'confirmadas' => Reservation::confirmada()->count(),
            'hoy'         => Reservation::whereDate('start_time', today())->count(),
            'esta_semana' => Reservation::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        $pendientes = Reservation::with('space')
            ->pendiente()
            ->orderBy('start_time')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'slug'       => $r->slug,
                'user_name'  => $r->user_name,
                'space_name' => $r->space->name,
                'start_time' => $r->start_time->toIso8601String(),
                'status'     => $r->status,
            ]);

        return Inertia::render('AdminDashboard', [
            'metrics'          => $metrics,
            'pendientes'       => $pendientes,
            'proximasReservas' => [],
        ]);
    }
}
