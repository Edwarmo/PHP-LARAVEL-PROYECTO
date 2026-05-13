<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Api\SpaceSlotsUseCase;
use App\Http\Controllers\Controller;
use App\Domain\Models\Space;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SpaceSlotsController extends Controller
{
    public function __construct(
        private readonly SpaceSlotsUseCase $spaceSlotsUseCase,
    ) {}

    public function __invoke(Request $request, Space $space): JsonResponse
    {
        $date        = $request->query('date', now()->toDateString());
        $slotMinutes = (int) env('RESERVATION_SLOT_MINUTES', 60);

        $slots = $this->spaceSlotsUseCase->getSlots($space, $date, $slotMinutes);

        return response()->json(['slots' => $slots]);
    }
}
