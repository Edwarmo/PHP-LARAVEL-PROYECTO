<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Availability;
use App\Models\Space;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AvailabilityModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function belongs_to_space(): void
    {
        $space = Space::factory()->create();
        $availability = Availability::factory()->create(['space_id' => $space->id]);
        $this->assertInstanceOf(Space::class, $availability->space);
        $this->assertTrue($availability->space->is($space));
    }

    #[Test]
    public function availability_service_resolved_as_singleton(): void
    {
        $instance = app(AvailabilityService::class);
        $this->assertInstanceOf(AvailabilityService::class, $instance);
        $this->assertSame($instance, app(AvailabilityService::class));
    }
}
