<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use App\Models\Space;
use App\Models\Reservation;
use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SupabaseIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Force PGSQL connection to hit real Supabase instance
        config(['database.default' => 'pgsql']);
        DB::purge('pgsql');
        DB::setDefaultConnection('pgsql');

        // Skip if Supabase isn't reachable (e.g., in CI or local dev with SQLite)
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->markTestSkipped('Supabase not available: ' . $e->getMessage());
        }

        $this->withoutMockingConsoleOutput();
    }

    // TEST 1: Can connect to Supabase
    public function test_supabase_connection_is_alive()
    {
        $pdo = DB::connection()->getPdo();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    // TEST 2: Can insert and read a Space
    public function test_can_create_and_read_space()
    {
        $space = Space::create([
            'name' => 'Test Space Boolean',
            'slug' => 'test-space-boolean-' . Str::random(10),
            'type' => 'auditorium',
            'capacity' => 10,
            'description' => 'Testing booleans',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('spaces', ['id' => $space->id]);
        $this->assertTrue($space->fresh()->is_active);
    }

    // TEST 3: Can insert and read a User
    public function test_can_create_and_read_user()
    {
        $email = 'user_' . Str::random(10) . '@example.com';
        
        $user = User::create([
            'name' => 'Integration User',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    // TEST 4: Can insert Availability linked to Space
    public function test_can_create_availability_for_space()
    {
        $space = Space::create([
            'name' => 'Avail Space',
            'slug' => 'avail-space-' . Str::random(10),
            'type' => 'meeting_room',
            'capacity' => 5,
            'description' => 'Avail',
            'is_active' => true,
        ]);

        Availability::create([
            'space_id' => $space->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->assertEquals(1, $space->availabilities()->count());
    }

    // TEST 5: Can create a Reservation linked to Space
    public function test_can_create_reservation_for_space()
    {
        $space = Space::create([
            'name' => 'Res Space',
            'slug' => 'res-space-' . Str::random(10),
            'type' => 'meeting_room',
            'capacity' => 5,
            'description' => 'Res',
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'space_id' => $space->id,
            'user_name' => 'Test Res',
            'user_email' => 'test-res@example.com',
            'start_time' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i:s'),
            'status' => 'pendiente',
        ]);

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
        $this->assertEquals('pendiente', $reservation->status);
    }

    // TEST 6: Reservation status transitions
    public function test_reservation_status_can_be_updated()
    {
        $space = Space::create([
            'name' => 'Trans Space',
            'slug' => 'trans-space-' . Str::random(10),
            'type' => 'meeting_room',
            'capacity' => 5,
            'description' => 'Trans',
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'space_id' => $space->id,
            'user_name' => 'Test Trans',
            'user_email' => 'test-trans@example.com',
            'start_time' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i:s'),
            'status' => 'pendiente',
        ]);

        $reservation->update(['status' => 'confirmada']);
        $this->assertEquals('confirmada', $reservation->fresh()->status);
    }

    // TEST 7: Slug must be unique
    public function test_space_slug_must_be_unique()
    {
        $slug = 'unique-slug-' . Str::random(10);
        
        Space::create([
            'name' => 'Space 1',
            'slug' => $slug,
            'type' => 'meeting_room',
            'capacity' => 10,
            'description' => '1',
            'is_active' => true,
        ]);
        
        try {
            Space::create([
                'name' => 'Space 2',
                'slug' => $slug,
                'type' => 'meeting_room',
                'capacity' => 10,
                'description' => '2',
                'is_active' => true,
            ]);
            $this->fail('Expected Exception (Unique Constraint Violation) was not thrown.');
        } catch (\Exception $e) {
            $this->assertTrue(
                str_contains(strtolower($e->getMessage()), 'unique') || 
                str_contains(strtolower($e->getMessage()), 'integrity constraint'),
                'Actual message: ' . $e->getMessage()
            );
        }
    }

    // TEST 8: Cannot double-book same slot
    public function test_overlapping_reservation_is_prevented()
    {
        $space = Space::create([
            'name' => 'Overlap Test',
            'slug' => 'overlap-test-' . Str::random(10),
            'type' => 'meeting_room',
            'capacity' => 5,
            'description' => 'Overlap test',
            'is_active' => true,
        ]);

        $monday = Carbon::now()->next(Carbon::MONDAY);
        
        Availability::create([
            'space_id' => $space->id,
            'day_of_week' => $monday->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
        ]);

        // First reservation (success)
        $payload1 = [
            'space_id'   => $space->id,
            'user_name'  => 'User 1',
            'user_email' => 'u1@example.com',
            'start_time' => $monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'end_time'   => $monday->copy()->setTime(12, 0)->format('Y-m-d H:i'),
        ];
        
        $response1 = $this->post(route('reservations.store'), $payload1);
        $response1->assertRedirect();
        
        // Confirm first reservation so it blocks the slot
        $reservation = Reservation::where('user_email', 'u1@example.com')->first();
        $reservation->update(['status' => 'confirmada']);

        // Second reservation (overlap)
        $payload2 = [
            'space_id'   => $space->id,
            'user_name'  => 'User 2',
            'user_email' => 'u2@example.com',
            'start_time' => $monday->copy()->setTime(11, 0)->format('Y-m-d H:i'),
            'end_time'   => $monday->copy()->setTime(13, 0)->format('Y-m-d H:i'),
        ];

        $response2 = $this->post(route('reservations.store'), $payload2);
        
        // Should have validation error for start_time due to overlap
        $response2->assertSessionHasErrors(['start_time']);
    }

    // TEST 9: All tables have data after seeding
    public function test_all_tables_have_data()
    {
        // To test this effectively without relying on manual seed execution,
        // we can call the seeder programmatically here, or just verify if DB has seed data.
        // Assuming the seeder has already run in Supabase:
        $this->assertTrue(Space::count() >= 0); // Making it resilient if run before seed
        
        // Or better yet, we can manually call the seeders if they are safe to run repeatedly:
        // However, the prompt says "Assert Space::count() > 0 (after seeder runs)".
        // So we will assume the seeder has already been run by the user.
        $this->assertGreaterThan(0, Space::count(), 'Spaces table is empty. Did you run the seeder?');
        $this->assertGreaterThan(0, User::count(), 'Users table is empty. Did you run the seeder?');
    }

    // TEST 10: Health endpoint returns ok
    public function test_health_endpoint()
    {
        $response = $this->get('/health');
        
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }
}
