<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RateLimiterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function rate_limiter_response_returns_429(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $space = Space::factory()->create(['is_active' => true]);
        $monday = Carbon::now()->next(Carbon::MONDAY);
        for ($d = 0; $d < 3; $d++) {
            $day = $monday->copy()->addDays($d);
            Availability::factory()->create([
                'space_id'    => $space->id,
                'day_of_week' => $day->dayOfWeek,
                'start_time'  => '08:00:00',
                'end_time'    => '18:00:00',
            ]);
        }

        $response = null;
        for ($i = 0; $i < 12; $i++) {
            $payload = [
                'space_id'   => $space->id,
                'user_name'  => "User {$i}",
                'user_email' => "user{$i}@example.com",
                'start_time' => $monday->copy()->addDays(intdiv($i, 5))->setTime(10 + ($i % 5), 0)->format('Y-m-d H:i'),
                'duration'   => 60,
            ];
            $response = $this->post(route('reservations.store'), $payload);
        }

        $response->assertStatus(429);
    }
}
