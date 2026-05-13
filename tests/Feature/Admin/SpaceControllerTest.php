<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SpaceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->get(route('admin.spaces.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_lists_spaces(): void
    {
        Space::factory()->count(3)->create();
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.spaces.index'));
        $response->assertStatus(200);
    }

    #[Test]
    public function create_shows_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.spaces.create'));
        $response->assertStatus(200);
    }

    #[Test]
    public function store_creates_space(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.spaces.store'), [
            'name'           => 'Nueva Sala Test',
            'type'           => 'conferencia',
            'capacity'       => 20,
            'price_per_hour' => 50000,
            'description'    => 'Una sala de prueba',
            'is_active'      => true,
        ]);
        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertDatabaseHas('spaces', ['name' => 'Nueva Sala Test']);
    }

    #[Test]
    public function store_generates_unique_slug(): void
    {
        Space::factory()->create(['slug' => 'nueva-sala-test', 'name' => 'Nueva Sala Test']);
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.spaces.store'), [
            'name'           => 'Nueva Sala Test',
            'type'           => 'reunión',
            'capacity'       => 10,
            'price_per_hour' => 30000,
        ]);
        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertDatabaseHas('spaces', ['slug' => 'nueva-sala-test-1']);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.spaces.store'), []);
        $response->assertSessionHasErrors(['name', 'type', 'capacity', 'price_per_hour']);
    }

    #[Test]
    public function edit_shows_form(): void
    {
        $space = Space::factory()->create();
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.spaces.edit', $space));
        $response->assertStatus(200);
    }

    #[Test]
    public function update_modifies_space(): void
    {
        $space = Space::factory()->create(['name' => 'Original Name']);
        $this->actingAs($this->admin);
        $response = $this->put(route('admin.spaces.update', $space), [
            'name'           => 'Updated Name',
            'type'           => 'webinar',
            'capacity'       => 30,
            'price_per_hour' => 75000,
            'description'    => 'Updated description',
            'is_active'      => true,
        ]);
        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertEquals('Updated Name', $space->fresh()->name);
    }

    #[Test]
    public function update_regenerates_slug_when_name_changes(): void
    {
        $space = Space::factory()->create(['name' => 'Original', 'slug' => 'original']);
        $this->actingAs($this->admin);
        $this->put(route('admin.spaces.update', $space), [
            'name'           => 'Updated Name',
            'type'           => 'webinar',
            'capacity'       => 30,
            'price_per_hour' => 75000,
            'is_active'      => true,
        ]);
        $this->assertEquals('updated-name', $space->fresh()->slug);
    }

    #[Test]
    public function update_keeps_slug_when_name_unchanged(): void
    {
        $space = Space::factory()->create(['name' => 'Same Name', 'slug' => 'same-name']);
        $this->actingAs($this->admin);
        $this->put(route('admin.spaces.update', $space), [
            'name'           => 'Same Name',
            'type'           => 'webinar',
            'capacity'       => 30,
            'price_per_hour' => 75000,
            'is_active'      => true,
        ]);
        $this->assertEquals('same-name', $space->fresh()->slug);
    }

    #[Test]
    public function update_validates_required_fields(): void
    {
        $space = Space::factory()->create();
        $this->actingAs($this->admin);
        $response = $this->put(route('admin.spaces.update', $space), []);
        $response->assertSessionHasErrors(['name', 'type', 'capacity', 'price_per_hour']);
    }

    #[Test]
    public function destroy_removes_space(): void
    {
        $space = Space::factory()->create();
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.spaces.destroy', $space));
        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertModelMissing($space);
    }

    #[Test]
    public function destroy_blocked_with_active_reservations(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->create([
            'space_id' => $space->id,
            'status'   => Reservation::STATUS_PENDIENTE,
        ]);
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.spaces.destroy', $space));
        $response->assertSessionHasErrors(['space']);
        $this->assertModelExists($space);
    }

    #[Test]
    public function destroy_blocked_with_confirmed_reservations(): void
    {
        $space = Space::factory()->create();
        Reservation::factory()->confirmada()->create([
            'space_id' => $space->id,
        ]);
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.spaces.destroy', $space));
        $response->assertSessionHasErrors(['space']);
        $this->assertModelExists($space);
    }

    #[Test]
    public function destroy_allows_without_active_reservations(): void
    {
        $space = Space::factory()->create();
        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.spaces.destroy', $space));
        $response->assertRedirect();
        $this->assertModelMissing($space);
    }

    #[Test]
    public function index_paginates_spaces(): void
    {
        Space::factory()->count(8)->create();
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.spaces.index'));
        $response->assertStatus(200);
    }
}
