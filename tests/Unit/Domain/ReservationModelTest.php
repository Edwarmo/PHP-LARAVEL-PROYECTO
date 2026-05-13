<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReservationModelTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        $this->space = Space::factory()->create();
    }

    #[Test]
    public function overlapping_scope_finds_overlapping_reservations(): void
    {
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time'   => Carbon::now()->addDay()->setHour(12),
        ]);
        $results = Reservation::overlapping(
            Carbon::now()->addDay()->setHour(11)->toDateTimeString(),
            Carbon::now()->addDay()->setHour(13)->toDateTimeString(),
        )->get();
        $this->assertCount(1, $results);
    }

    #[Test]
    public function overlapping_scope_excludes_non_overlapping(): void
    {
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time'   => Carbon::now()->addDay()->setHour(12),
        ]);
        $results = Reservation::overlapping(
            Carbon::now()->addDay()->setHour(13)->toDateTimeString(),
            Carbon::now()->addDay()->setHour(14)->toDateTimeString(),
        )->get();
        $this->assertCount(0, $results);
    }

    #[Test]
    public function duration_in_hours_returns_correct_value(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setTime(10, 0),
            'end_time'   => Carbon::now()->addDay()->setTime(12, 30),
        ]);
        $this->assertEquals(2.5, $reservation->duration_in_hours);
    }

    #[Test]
    public function duration_in_hours_for_one_hour(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setTime(10, 0),
            'end_time'   => Carbon::now()->addDay()->setTime(11, 0),
        ]);
        $this->assertEquals(1.0, $reservation->duration_in_hours);
    }

    #[Test]
    public function pendiente_scope_filters_correctly(): void
    {
        Reservation::factory()->count(2)->create(['space_id' => $this->space->id, 'status' => Reservation::STATUS_PENDIENTE]);
        Reservation::factory()->confirmada()->create(['space_id' => $this->space->id]);
        $this->assertEquals(2, Reservation::pendiente()->count());
    }

    #[Test]
    public function confirmada_scope_filters_correctly(): void
    {
        Reservation::factory()->count(3)->create(['space_id' => $this->space->id, 'status' => Reservation::STATUS_PENDIENTE]);
        Reservation::factory()->confirmada()->count(2)->create(['space_id' => $this->space->id]);
        $this->assertEquals(2, Reservation::confirmada()->count());
    }
}
