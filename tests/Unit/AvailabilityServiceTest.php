<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Models\Availability;
use App\Domain\Models\BlockedSlot;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Infrastructure\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests unitarios para AvailabilityService.
 *
 * Cubre los 4 escenarios críticos del sistema de colisiones:
 *   1. Slot libre          → isSlotAvailable retorna true
 *   2. Colisión reserva    → isSlotAvailable retorna false
 *   3. Colisión bloqueo    → isSlotAvailable retorna false
 *   4. Fuera de horario    → isSlotAvailable retorna false
 */
final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $service;
    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar slot de 60 minutos
        config(['app.reservation_slot_minutes' => 60]);

        $this->service = AvailabilityService::make();

        // Crear sala base con disponibilidad Lun–Vie 08:00–18:00
        $this->space = Space::factory()->create(['is_active' => true]);

        // Día de prueba: usamos el próximo lunes a las 10:00
        $monday = Carbon::now()->next(Carbon::MONDAY);

        Availability::factory()->create([
            'space_id'    => $this->space->id,
            'day_of_week' => $monday->dayOfWeek,   // 1 = Lunes
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  Escenario 1 — Slot libre
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function slot_libre_retorna_true(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'Un slot sin reservas ni bloqueos debe estar disponible.');
    }

    // ══════════════════════════════════════════════════════════════
    //  Escenario 2 — Colisión con reserva activa
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function slot_ocupado_por_reserva_retorna_false(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        // Crear reserva confirmada que ocupa ese slot
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $start->copy()->subMinutes(30),  // 09:30
            'end_time'   => $end->copy()->addMinutes(30),    // 11:30 → solapa
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot solapado con una reserva confirmada debe retornar false.');
    }

    #[Test]
    public function slot_ocupado_por_reserva_pendiente_retorna_false(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(14, 0);
        $end    = $monday->copy()->setTime(15, 0);

        Reservation::factory()->create([  // estado pendiente por defecto
            'space_id'   => $this->space->id,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot solapado con una reserva pendiente debe retornar false.');
    }

    #[Test]
    public function reserva_rechazada_no_bloquea_slot(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        // Reserva rechazada NO debe bloquear
        Reservation::factory()->rechazada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'Una reserva rechazada no debe bloquear el slot.');
    }

    // ══════════════════════════════════════════════════════════════
    //  Escenario 3 — Colisión con blocked_slot
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function slot_ocupado_por_bloqueo_manual_retorna_false(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(9, 0);
        $end    = $monday->copy()->setTime(10, 0);

        BlockedSlot::factory()->create([
            'space_id'   => $this->space->id,
'start_time' => $monday->copy()->setTime(8, 30),
        'end_time'   => $monday->copy()->setTime(9, 30),  // solapa
            'reason'     => 'Mantenimiento de equipos',
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot solapado con un bloqueo manual debe retornar false.');
    }

    #[Test]
    public function bloqueo_contiguo_no_solapado_no_bloquea(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        // Bloqueo termina justo cuando empieza el slot → no solapa
        BlockedSlot::factory()->create([
            'space_id'   => $this->space->id,
'start_time' => $monday->copy()->setTime(9, 0),
        'end_time'   => $monday->copy()->setTime(10, 0),
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'Un bloqueo contiguo pero no solapado no debe bloquear el slot.');
    }

    // ══════════════════════════════════════════════════════════════
    //  Escenario 4 — Fuera de horario comercial
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function slot_fuera_de_horario_semanal_retorna_false(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);

        // 19:00–20:00 → fuera del horario 08:00–18:00
        $start = $monday->copy()->setTime(19, 0);
        $end   = $monday->copy()->setTime(20, 0);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot fuera del horario configurado debe retornar false.');
    }

    #[Test]
    public function slot_en_dia_sin_disponibilidad_retorna_false(): void
    {
        // Usamos domingo (día 0) — la sala solo tiene disponibilidad lunes
        $sunday = Carbon::now()->next(Carbon::SUNDAY);
        $start  = $sunday->copy()->setTime(10, 0);
        $end    = $sunday->copy()->setTime(11, 0);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot en un día sin disponibilidad configurada debe retornar false.');
    }

    #[Test]
    public function slot_que_cruza_limite_de_horario_retorna_false(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);

        // 17:30–18:30 → excede el límite de 18:00
        $start = $monday->copy()->setTime(17, 30);
        $end   = $monday->copy()->setTime(18, 30);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertFalse($result, 'Un slot que excede el límite de horario no debe estar disponible.');
    }

    // ══════════════════════════════════════════════════════════════
    //  getAvailableSlots — casos base
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function get_available_slots_retorna_todos_los_slots_del_dia(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $slots  = $this->service->getAvailableSlots($this->space, $monday);

        // 08:00–18:00 con slots de 60 min = 10 slots
        $this->assertCount(10, $slots);
        $this->assertEquals('08:00 – 09:00', $slots->first()['label']);
        $this->assertEquals('17:00 – 18:00', $slots->last()['label']);
    }

    #[Test]
    public function get_available_slots_excluye_slots_con_reserva(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);

        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $monday->copy()->setTime(10, 0),
            'end_time'   => $monday->copy()->setTime(11, 0),
        ]);

        $slots = $this->service->getAvailableSlots($this->space, $monday);

        $this->assertCount(10, $slots);
        $this->assertFalse(
            $slots->firstWhere('label', '10:00 – 11:00')['available'],
            'El slot 10:00–11:00 debe estar marcado como no disponible por la reserva confirmada.'
        );
    }

    #[Test]
    public function get_available_slots_retorna_vacio_sin_disponibilidad(): void
    {
        $sunday = Carbon::now()->next(Carbon::SUNDAY);
        $slots  = $this->service->getAvailableSlots($this->space, $sunday);

        $this->assertTrue($slots->isEmpty());
    }

    // ══════════════════════════════════════════════════════════════
    //  getNextAvailableDays
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function get_next_available_days_retorna_dias_con_slots(): void
    {
        $days = $this->service->getNextAvailableDays($this->space, 7);

        $this->assertNotEmpty($days);
        $days->each(function (array $day) {
            $this->assertArrayHasKey('date', $day);
            $this->assertArrayHasKey('available_slots_count', $day);
            $this->assertGreaterThan(0, $day['available_slots_count']);
        });
    }

    #[Test]
    public function isSlotAvailable_lanza_excepcion_si_start_mayor_que_end(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $monday = Carbon::now()->next(Carbon::MONDAY);
        $this->service->isSlotAvailable(
            $this->space,
            $monday->copy()->setTime(11, 0),
            $monday->copy()->setTime(10, 0),
        );
    }

    #[Test]
    public function get_next_available_days_retorna_vacio_sin_availabilities(): void
    {
        $spaceSinDisp = Space::factory()->create();

        $days = $this->service->getNextAvailableDays($spaceSinDisp, 7);

        $this->assertTrue($days->isEmpty());
    }

    #[Test]
    public function slot_exactamente_al_inicio_del_horario_esta_disponible(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(8, 0);
        $end    = $monday->copy()->setTime(9, 0);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'El slot exactamente al inicio del horario debe estar disponible.');
    }

    #[Test]
    public function slot_exactamente_al_final_del_horario_esta_disponible(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(17, 0);
        $end    = $monday->copy()->setTime(18, 0);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'El slot exactamente al final del horario debe estar disponible.');
    }

    #[Test]
    public function get_available_slots_con_slots_de_30_minutos(): void
    {
        config(['app.reservation_slot_minutes' => 30]);

        $monday = Carbon::now()->next(Carbon::MONDAY);
        $slots  = $this->service->getAvailableSlots($this->space, $monday);

        // 08:00–18:00 con slots de 30 min = 20 slots
        $this->assertCount(20, $slots);
        $this->assertEquals('08:00 – 08:30', $slots->first()['label']);
        $this->assertEquals('17:30 – 18:00', $slots->last()['label']);
    }

    #[Test]
    public function reserva_con_status_cancelada_no_bloquea_slot(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        Reservation::factory()->cancelada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'Una reserva cancelada no debe bloquear el slot.');
    }

    #[Test]
    public function reserva_con_status_finalizada_no_bloquea_slot(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);
        $start  = $monday->copy()->setTime(10, 0);
        $end    = $monday->copy()->setTime(11, 0);

        Reservation::factory()->finalizada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $start,
            'end_time'   => $end,
        ]);

        $result = $this->service->isSlotAvailable($this->space, $start, $end);

        $this->assertTrue($result, 'Una reserva finalizada no debe bloquear el slot.');
    }

    #[Test]
    public function multiple_reservas_en_el_mismo_dia_bloquean_slots_correctamente(): void
    {
        $monday = Carbon::now()->next(Carbon::MONDAY);

        // Reservas en 09:00-10:00 y 14:00-15:00
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $monday->copy()->setTime(9, 0),
            'end_time'   => $monday->copy()->setTime(10, 0),
        ]);
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $monday->copy()->setTime(14, 0),
            'end_time'   => $monday->copy()->setTime(15, 0),
        ]);

        $slots = $this->service->getAvailableSlots($this->space, $monday);

        $this->assertCount(10, $slots);
        $this->assertFalse($slots->firstWhere('label', '09:00 – 10:00')['available']);
        $this->assertFalse($slots->firstWhere('label', '14:00 – 15:00')['available']);
        $this->assertTrue($slots->firstWhere('label', '10:00 – 11:00')['available']);
        $this->assertTrue($slots->firstWhere('label', '13:00 – 14:00')['available']);
    }
}
