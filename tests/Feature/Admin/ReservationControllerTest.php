<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Events\ReservationStatusChanged;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->space = Space::factory()->create();
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->get(route('admin.reservations.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_lists_reservations(): void
    {
        Reservation::factory()->count(3)->create(['space_id' => $this->space->id]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.index'));
        $response->assertStatus(200);
    }

    #[Test]
    public function index_with_status_filter_returns_status_200(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.index', ['status' => 'pendiente']));
        $response->assertStatus(200);
    }

    #[Test]
    public function index_filters_by_space_id(): void
    {
        $space2 = Space::factory()->create();
        Reservation::factory()->create(['space_id' => $this->space->id]);
        Reservation::factory()->create(['space_id' => $space2->id]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.index', ['space_id' => $this->space->id]));
        $response->assertStatus(200);
    }

    #[Test]
    public function index_filters_by_date_range(): void
    {
        Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addMonth(),
            'end_time'   => Carbon::now()->addMonth()->addHour(),
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.index', [
            'from' => Carbon::now()->toDateString(),
            'to'   => Carbon::now()->addDays(60)->toDateString(),
        ]));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_displays_reservation(): void
    {
        $reservation = Reservation::factory()->create(['space_id' => $this->space->id]);
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.show', $reservation->slug));
        $response->assertStatus(200);
    }

    #[Test]
    public function show_404_for_invalid_slug(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reservations.show', 'non-existent'));
        $response->assertStatus(404);
    }

    #[Test]
    public function update_changes_status(): void
    {
        Event::fake();
        $reservation = Reservation::factory()->create([
            'space_id'  => $this->space->id,
            'status'    => Reservation::STATUS_PENDIENTE,
            'user_name' => 'Original Name',
            'user_email' => 'original@example.com',
        ]);
        $this->actingAs($this->admin);
        $response = $this->put(route('admin.reservations.update', $reservation->slug), [
            'status'     => Reservation::STATUS_CONFIRMADA,
            'user_name'  => 'Original Name',
            'user_email' => 'original@example.com',
        ]);
        $response->assertRedirect();
        $this->assertEquals(Reservation::STATUS_CONFIRMADA, $reservation->fresh()->status);
        Event::assertDispatched(ReservationStatusChanged::class);
    }

    #[Test]
    public function update_without_status_change_does_not_dispatch_event(): void
    {
        Event::fake();
        $reservation = Reservation::factory()->create([
            'space_id'   => $this->space->id,
            'status'     => Reservation::STATUS_PENDIENTE,
            'user_name'  => 'Original Name',
            'user_email' => 'original@example.com',
        ]);
        $this->actingAs($this->admin);
        $this->put(route('admin.reservations.update', $reservation->slug), [
            'status'     => Reservation::STATUS_PENDIENTE,
            'user_name'  => 'Original Name',
            'user_email' => 'original@example.com',
        ]);
        Event::assertNotDispatched(ReservationStatusChanged::class);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $reservation = Reservation::factory()->create(['space_id' => $this->space->id]);
        $this->actingAs($this->admin);
        $response = $this->put(route('admin.reservations.update', $reservation->slug), []);
        $response->assertSessionHasErrors(['status', 'user_name', 'user_email']);
    }

    #[Test]
    public function destroy_deletes_reservation(): void
    {
        $reservation = Reservation::factory()->create(['space_id' => $this->space->id]);
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.reservations.destroy', $reservation->slug));
        $response->assertRedirect(route('admin.reservations.index'));
        $this->assertModelMissing($reservation);
    }

    #[Test]
    public function destroy_404_for_invalid_slug(): void
    {
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.reservations.destroy', 'non-existent'));
        $response->assertStatus(404);
    }
}
