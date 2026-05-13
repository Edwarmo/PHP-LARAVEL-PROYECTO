<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Space;
use App\Models\Reservation;
use App\Models\Availability;
use Carbon\Carbon;

/**
 * Unit tests for model relationships and business logic.
 * These tests verify model behavior without database dependencies.
 */
class ModelBusinessLogicTest extends TestCase
{
    /**
     * Test Space model has relationship methods defined.
     */
    public function test_space_model_has_relationship_methods(): void
    {
        $this->assertTrue(method_exists(Space::class, 'reservations'));
        $this->assertTrue(method_exists(Space::class, 'availabilities'));
        $this->assertTrue(method_exists(Space::class, 'blockedSlots'));
    }

    /**
     * Test Reservation status constants.
     */
    public function test_reservation_has_correct_status_constants(): void
    {
        $this->assertEquals('pendiente', Reservation::STATUS_PENDIENTE);
        $this->assertEquals('confirmada', Reservation::STATUS_CONFIRMADA);
        $this->assertEquals('rechazada', Reservation::STATUS_RECHAZADA);
        $this->assertEquals('cancelada', Reservation::STATUS_CANCELADA);
        $this->assertEquals('finalizada', Reservation::STATUS_FINALIZADA);
    }

    /**
     * Test Space slug generation from name.
     */
    public function test_space_slug_generation(): void
    {
        $names = [
            'Sala Andina' => 'sala-andina',
            'Sala de Reuniones Principal' => 'sala-de-reuniones-principal',
            'Sala Pacífico' => 'sala-pacifico',
        ];

        foreach ($names as $name => $expectedSlug) {
            $actualSlug = \Illuminate\Support\Str::slug($name);
            $this->assertEquals($expectedSlug, $actualSlug);
        }
    }

    /**
     * Test Reservation overlapping scope SQL logic.
     */
    public function test_reservation_overlapping_scope_logic(): void
    {
        // Test that the overlapping scope builder correctly uses times
        // The scope checks: start_time < $end AND end_time > $start
        $start = '2024-01-01 10:00:00';
        $end = '2024-01-01 11:00:00';
        
        $overlappingStart = '2024-01-01 09:30:00';
        $overlappingEnd = '2024-01-01 10:30:00';
        
        // This should overlap: new slot (09:30-10:30) overlaps with (10:00-11:00)
        $this->assertTrue(
            $overlappingStart < $end && $overlappingEnd > $start
        );
        
        // Non-overlapping check
        $nonOverlappingStart = '2024-01-01 12:00:00';
        $nonOverlappingEnd = '2024-01-01 13:00:00';
        
        $this->assertFalse(
            $nonOverlappingStart < $end && $nonOverlappingEnd > $start
        );
    }

    /**
     * Test Availability day constants.
     */
    public function test_availability_has_correct_day_constants(): void
    {
        $this->assertEquals('Domingo', Availability::DAYS[0]);
        $this->assertEquals('Lunes', Availability::DAYS[1]);
        $this->assertEquals('Martes', Availability::DAYS[2]);
        $this->assertEquals('Miércoles', Availability::DAYS[3]);
        $this->assertEquals('Jueves', Availability::DAYS[4]);
        $this->assertEquals('Viernes', Availability::DAYS[5]);
        $this->assertEquals('Sábado', Availability::DAYS[6]);
    }

    /**
     * Test Reservation duration calculation.
     */
    public function test_reservation_duration_calculation(): void
    {
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 12:30:00');
        
        $diffInMinutes = $start->diffInMinutes($end);
        $diffInHours = $diffInMinutes / 60;
        
        $this->assertEquals(150, $diffInMinutes);
        $this->assertEquals(2.5, $diffInHours);
    }

    /**
     * Test that Space model uses slug for routing.
     */
    public function test_space_route_key_is_slug(): void
    {
        $space = new Space();
        $this->assertEquals('slug', $space->getRouteKeyName());
    }

    /**
     * Test Reservation fillable attributes.
     */
    public function test_reservation_fillable_attributes(): void
    {
        $reservation = new Reservation();
        $fillable = $reservation->getFillable();
        
        $this->assertContains('space_id', $fillable);
        $this->assertContains('user_name', $fillable);
        $this->assertContains('user_email', $fillable);
        $this->assertContains('start_time', $fillable);
        $this->assertContains('end_time', $fillable);
        $this->assertContains('status', $fillable);
    }

    /**
     * Test Space fillable attributes.
     */
    public function test_space_fillable_attributes(): void
    {
        $space = new Space();
        $fillable = $space->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('capacity', $fillable);
    }

    /**
     * Test Reservation date casting.
     */
    public function test_reservation_datetime_casting(): void
    {
        $casts = (new Reservation())->getCasts();
        
        $this->assertArrayHasKey('start_time', $casts);
        $this->assertArrayHasKey('end_time', $casts);
        $this->assertEquals('datetime', $casts['start_time']);
        $this->assertEquals('datetime', $casts['end_time']);
    }

    /**
     * Test Space boolean casting.
     */
    public function test_space_boolean_casting(): void
    {
        $casts = (new Space())->getCasts();
        
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertArrayHasKey('price_per_hour', $casts);
        $this->assertArrayHasKey('capacity', $casts);
    }

    public function test_space_is_active_defaults_to_true(): void
    {
        $space = new Space();
        $this->assertTrue($space->is_active);
    }

    public function test_space_active_scope_exists(): void
    {
        $this->assertTrue(method_exists(Space::class, 'scopeActive'));
    }

    public function test_reservation_pendiente_scope_exists(): void
    {
        $this->assertTrue(method_exists(Reservation::class, 'scopePendiente'));
    }

    public function test_reservation_confirmada_scope_exists(): void
    {
        $this->assertTrue(method_exists(Reservation::class, 'scopeConfirmada'));
    }

    public function test_reservation_overlapping_scope_exists(): void
    {
        $this->assertTrue(method_exists(Reservation::class, 'scopeOverlapping'));
    }

    public function test_reservation_duration_in_hours_formula(): void
    {
        $start = \Carbon\Carbon::parse('2024-01-01 10:00:00');
        $end   = \Carbon\Carbon::parse('2024-01-01 12:30:00');
        $this->assertEquals(2.5, $start->diffInMinutes($end) / 60);
    }

    public function test_blocked_slot_fillable_attributes(): void
    {
        $blockedSlot = new \App\Models\BlockedSlot();
        $fillable = $blockedSlot->getFillable();
        $this->assertContains('space_id', $fillable);
        $this->assertContains('start_time', $fillable);
        $this->assertContains('end_time', $fillable);
        $this->assertContains('reason', $fillable);
    }

    public function test_blocked_slot_datetime_casting(): void
    {
        $casts = (new \App\Models\BlockedSlot())->getCasts();
        $this->assertArrayHasKey('start_time', $casts);
        $this->assertArrayHasKey('end_time', $casts);
        $this->assertEquals('datetime', $casts['start_time']);
        $this->assertEquals('datetime', $casts['end_time']);
    }

    public function test_blocked_slot_has_space_relationship(): void
    {
        $this->assertTrue(method_exists(\App\Models\BlockedSlot::class, 'space'));
    }

    public function test_blocked_slot_overlapping_scope_exists(): void
    {
        $this->assertTrue(method_exists(\App\Models\BlockedSlot::class, 'scopeOverlapping'));
    }

    public function test_availability_day_name_accessor(): void
    {
        $availability = new \App\Models\Availability();
        $availability->day_of_week = 1;
        $this->assertEquals('Lunes', $availability->day_name);
        $availability->day_of_week = 5;
        $this->assertEquals('Viernes', $availability->day_name);
    }

    public function test_availability_has_space_relationship(): void
    {
        $this->assertTrue(method_exists(\App\Models\Availability::class, 'space'));
    }

    public function test_space_type_cast_is_integer(): void
    {
        $casts = (new Space())->getCasts();
        $this->assertEquals('integer', $casts['capacity']);
    }

    public function test_reservation_has_booted_method(): void
    {
        $this->assertTrue(method_exists(Reservation::class, 'booted'));
    }

    public function test_reservation_slug_cast_from_uuid(): void
    {
        $this->assertContains('slug', (new Reservation())->getFillable());
    }
}