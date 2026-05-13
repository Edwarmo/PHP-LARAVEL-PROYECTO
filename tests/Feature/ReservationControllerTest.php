<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->space = Space::factory()->create(['is_active' => true]);
    }

    #[Test]
    public function create_shows_form(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.create'));
        $response->assertStatus(200);
    }

    #[Test]
    public function create_with_space_pre_selected(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.create', [
            'space' => $this->space->slug,
            'start' => '2026-06-01T10:00:00',
            'duration' => 60,
        ]));
        $response->assertStatus(200);
    }

    #[Test]
    public function create_with_invalid_space_returns_404(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.create', ['space' => 'non-existent']));
        $response->assertStatus(404);
    }

    #[Test]
    public function show_displays_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'user_email' => $this->user->email,
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.show', $reservation->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_404_for_invalid_slug(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.show', 'non-existent'));
        $response->assertStatus(404);
    }

    #[Test]
    public function show_shows_user_reservation_history(): void
    {
        $reservation = Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'user_email' => 'history@example.com',
        ]);
        Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'user_email' => 'history@example.com',
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.show', $reservation->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function history_displays_user_reservations(): void
    {
        Reservation::factory()->count(3)->create([
            'space_id'   => $this->space->id,
            'user_email' => 'test@example.com',
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.history', ['email' => 'test@example.com']));
        $response->assertStatus(200);
    }

    #[Test]
    public function history_uses_logged_in_user_email_by_default(): void
    {
        Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'user_email' => $this->user->email,
        ]);
        $this->actingAs($this->user);
        $response = $this->get(route('reservations.history'));
        $response->assertStatus(200);
    }

    #[Test]
    public function history_returns_empty_when_no_email(): void
    {
        $response = $this->get(route('reservations.history'));
        $response->assertStatus(302);
    }

    #[Test]
    public function show_requires_authentication(): void
    {
        $response = $this->get(route('reservations.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function history_requires_authentication(): void
    {
        $response = $this->get(route('reservations.history'));
        $response->assertRedirect(route('login'));
    }
}
