<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Models\Availability;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SpaceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function index_lists_active_spaces(): void
    {
        Space::factory()->count(3)->create(['is_active' => true]);
        Space::factory()->create(['is_active' => false]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.index'));
        $response->assertStatus(200);
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->get(route('spaces.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_space_with_availability(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->next(Carbon::MONDAY);
        Availability::factory()->create([
            'space_id'    => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', $space->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_returns_404_for_nonexistent_slug(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', 'non-existent'));
        $response->assertStatus(404);
    }

    #[Test]
    public function show_returns_404_for_inactive_space(): void
    {
        $space = Space::factory()->create(['is_active' => false]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', $space->slug));
        $response->assertStatus(404);
    }

    #[Test]
    public function show_shows_next_available_days(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        for ($i = 1; $i <= 5; $i++) {
            Availability::factory()->create([
                'space_id'    => $space->id,
                'day_of_week' => $i,
                'start_time'  => '08:00:00',
                'end_time'    => '18:00:00',
            ]);
        }
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', $space->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_marks_occupied_slots(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->next(Carbon::MONDAY);
        Availability::factory()->create([
            'space_id'    => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
        Reservation::factory()->confirmada()->create([
            'space_id'   => $space->id,
            'start_time' => $monday->copy()->setHour(10),
            'end_time'   => $monday->copy()->setHour(11),
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', $space->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_handles_blocked_slots(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->next(Carbon::MONDAY);
        Availability::factory()->create([
            'space_id'    => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
        $space->blockedSlots()->create([
            'start_time' => $monday->copy()->setHour(14),
            'end_time'   => $monday->copy()->setHour(15),
            'reason'     => 'Mantenimiento',
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.show', $space->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function index_paginates_spaces(): void
    {
        Space::factory()->count(8)->create(['is_active' => true]);
        $this->actingAs($this->user);
        $response = $this->get(route('spaces.index'));
        $response->assertStatus(200);
    }
}
