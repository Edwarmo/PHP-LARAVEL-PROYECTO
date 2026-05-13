<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Create a space and authenticate because / requires auth
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        \App\Models\Space::factory()->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}