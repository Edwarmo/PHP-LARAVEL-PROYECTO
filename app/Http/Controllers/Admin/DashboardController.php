<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\UseCases\Admin\DashboardUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardUseCase $dashboardUseCase,
    ) {}

    public function __invoke(Request $request): Response
    {
        return Inertia::render('AdminDashboard', $this->dashboardUseCase->getDashboardData());
    }
}
