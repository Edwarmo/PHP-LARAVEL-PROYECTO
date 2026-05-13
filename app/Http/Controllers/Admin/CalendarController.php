<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\UseCases\Admin\CalendarUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarUseCase $calendarUseCase,
    ) {}

    public function __invoke(Request $request): Response
    {
        $spaceId   = $request->query('space_id') ? (int) $request->query('space_id') : null;
        $weekParam = $request->query('week');

        return Inertia::render('AdminCalendar',
            $this->calendarUseCase->getCalendarData($spaceId, $weekParam)
        );
    }
}
