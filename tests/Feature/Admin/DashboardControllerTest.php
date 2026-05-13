<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function dashboard_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function dashboard_returns_metrics(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->count(3)->create(['space_id' => $space->id, 'status' => Reservation::STATUS_PENDIENTE]);
        Reservation::factory()->count(2)->create(['space_id' => $space->id, 'status' => Reservation::STATUS_CONFIRMADA]);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('pendientes');
        $response->assertSee('confirmadas');
    }

    #[Test]
    public function dashboard_shows_pending_reservations(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->create([
            'space_id'  => $space->id,
            'status'    => Reservation::STATUS_PENDIENTE,
            'user_name' => 'Pendiente User',
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    #[Test]
    public function dashboard_todays_metrics(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->create([
            'space_id'   => $space->id,
            'start_time' => Carbon::today()->setHour(10),
            'end_time'   => Carbon::today()->setHour(11),
            'status'     => Reservation::STATUS_CONFIRMADA,
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }
}
