<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;

class SpaceModelLogicTest extends TestCase
{
    public function test_slug_generation_logic(): void
    {
        $name = 'Sala de Reuniones Principal';
        $expectedSlug = 'sala-de-reuniones-principal';
        
        $this->assertEquals(
            $expectedSlug,
            \Illuminate\Support\Str::slug($name)
        );
    }

    public function test_reservation_status_constants(): void
    {
        $this->assertEquals('pendiente', \App\Models\Reservation::STATUS_PENDIENTE);
        $this->assertEquals('confirmada', \App\Models\Reservation::STATUS_CONFIRMADA);
        $this->assertEquals('rechazada', \App\Models\Reservation::STATUS_RECHAZADA);
        $this->assertEquals('cancelada', \App\Models\Reservation::STATUS_CANCELADA);
        $this->assertEquals('finalizada', \App\Models\Reservation::STATUS_FINALIZADA);
    }
}