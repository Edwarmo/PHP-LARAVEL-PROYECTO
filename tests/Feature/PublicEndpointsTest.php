<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    #[Test]
    public function api_slots_returns_empty_when_no_availability(): void
    {
        $space = Space::factory()->create();

        $response = $this->getJson("/api/spaces/{$space->slug}/slots?date=2026-05-18");

        $response->assertOk();
        $response->assertJson(['slots' => []]);
    }

    #[Test]
    public function api_slots_returns_slots_for_available_day(): void
    {
        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::parse('2026-05-18');

        Availability::factory()->create([
            'space_id'    => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);

        $response = $this->getJson("/api/spaces/{$space->slug}/slots?date=2026-05-18");

        $response->assertOk();
        $response->assertJsonCount(10, 'slots');
        $response->assertJsonPath('slots.0.label', '08:00 – 09:00');
        $response->assertJsonPath('slots.9.label', '17:00 – 18:00');
    }

    #[Test]
    public function api_slots_returns_404_for_invalid_space(): void
    {
        $response = $this->getJson('/api/spaces/non-existent-space/slots?date=2026-05-18');
        $response->assertNotFound();
    }
}
