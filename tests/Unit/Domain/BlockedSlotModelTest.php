<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Models\BlockedSlot;
use App\Domain\Models\Space;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BlockedSlotModelTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        $this->space = Space::factory()->create();
    }

    #[Test]
    public function belongs_to_space(): void
    {
        $blocked = BlockedSlot::factory()->create(['space_id' => $this->space->id]);
        $this->assertInstanceOf(Space::class, $blocked->space);
        $this->assertTrue($blocked->space->is($this->space));
    }

    #[Test]
    public function overlapping_scope_finds_overlapping_slots(): void
    {
        BlockedSlot::factory()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time'   => Carbon::now()->addDay()->setHour(12),
        ]);
        $results = BlockedSlot::overlapping(
            Carbon::now()->addDay()->setHour(11)->toDateTimeString(),
            Carbon::now()->addDay()->setHour(13)->toDateTimeString(),
        )->get();
        $this->assertCount(1, $results);
    }

    #[Test]
    public function overlapping_scope_excludes_non_overlapping(): void
    {
        BlockedSlot::factory()->create([
            'space_id'   => $this->space->id,
            'start_time' => Carbon::now()->addDay()->setHour(10),
            'end_time'   => Carbon::now()->addDay()->setHour(12),
        ]);
        $results = BlockedSlot::overlapping(
            Carbon::now()->addDay()->setHour(13)->toDateTimeString(),
            Carbon::now()->addDay()->setHour(14)->toDateTimeString(),
        )->get();
        $this->assertCount(0, $results);
    }
}
