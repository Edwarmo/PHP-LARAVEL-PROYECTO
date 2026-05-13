<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FinalizeReservationsCommandTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        $this->space = Space::factory()->create();
    }

    #[Test]
    public function command_finalizes_past_confirmed_reservations(): void
    {
        Reservation::factory()->confirmada()->pasada()->create([
            'space_id' => $this->space->id,
        ]);
        $this->artisan('reservations:finalize', ['--no-interaction' => true])
            ->assertSuccessful();
        $this->assertEquals(
            Reservation::STATUS_FINALIZADA,
            Reservation::first()->fresh()->status
        );
    }

    #[Test]
    public function command_does_not_finalize_future_reservations(): void
    {
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDays(10),
            'end_time'   => Carbon::now()->addDays(10)->addHour(),
        ]);
        $this->artisan('reservations:finalize', ['--no-interaction' => true])
            ->assertSuccessful();
        $this->assertEquals(
            Reservation::STATUS_CONFIRMADA,
            Reservation::first()->fresh()->status
        );
    }

    #[Test]
    public function command_outputs_when_no_reservations_to_finalize(): void
    {
        $this->artisan('reservations:finalize', ['--no-interaction' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('No hay reservas pendientes de finalizar');
    }

    #[Test]
    public function command_dry_run_does_not_update(): void
    {
        Reservation::factory()->confirmada()->pasada()->create([
            'space_id' => $this->space->id,
        ]);
        $this->artisan('reservations:finalize --dry-run --no-interaction')
            ->assertSuccessful();
        $this->assertEquals(
            Reservation::STATUS_CONFIRMADA,
            Reservation::first()->fresh()->status
        );
    }

    #[Test]
    public function command_does_not_finalize_pendiente_reservations(): void
    {
        Reservation::factory()->pasada()->create([
            'space_id' => $this->space->id,
            'status'   => Reservation::STATUS_PENDIENTE,
        ]);
        $this->artisan('reservations:finalize', ['--no-interaction' => true])
            ->assertSuccessful();
        $this->assertEquals(
            Reservation::STATUS_PENDIENTE,
            Reservation::first()->fresh()->status
        );
    }

    #[Test]
    public function command_finalizes_multiple_reservations(): void
    {
        Reservation::factory()->confirmada()->pasada()->count(3)->create([
            'space_id' => $this->space->id,
        ]);
        $this->artisan('reservations:finalize', ['--no-interaction' => true])
            ->assertSuccessful();
        $this->assertEquals(3, Reservation::where('status', Reservation::STATUS_FINALIZADA)->count());
    }
}
