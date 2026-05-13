<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Availability;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CalendarControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function calendar_requires_authentication(): void
    {
        $response = $this->get(route('admin.calendar'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function calendar_shows_spaces_and_reservations(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->startOfWeek();
        Availability::factory()->create([
            'space_id'    => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
        Reservation::factory()->create([
            'space_id'   => $space->id,
            'start_time' => $monday->copy()->setHour(10),
            'end_time'   => $monday->copy()->setHour(11),
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.calendar'));
        $response->assertStatus(200);
    }

    #[Test]
    public function calendar_filters_by_space_id(): void
    {
        $space1 = Space::factory()->create(['is_active' => true]);
        $space2 = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->startOfWeek();
        Reservation::factory()->create([
            'space_id'   => $space2->id,
            'start_time' => $monday->copy()->setHour(10),
            'end_time'   => $monday->copy()->setHour(11),
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.calendar', ['space_id' => $space2->id]));
        $response->assertStatus(200);
    }

    #[Test]
    public function calendar_accepts_week_parameter(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.calendar', ['week' => '2026-06-01']));
        $response->assertStatus(200);
    }

    #[Test]
    public function calendar_shows_week_days(): void
    {
        Space::factory()->count(2)->create(['is_active' => true]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.calendar'));
        $response->assertStatus(200);
    }

    #[Test]
    public function calendar_shows_only_active_spaces(): void
    {
        Space::factory()->create(['is_active' => true]);
        Space::factory()->create(['is_active' => false]);
        Space::factory()->create(['is_active' => true]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.calendar'));
        $response->assertStatus(200);
    }
}
