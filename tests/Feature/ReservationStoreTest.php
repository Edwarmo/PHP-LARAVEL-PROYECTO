<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ReservationCreated;
use App\Domain\Models\Availability;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use App\Domain\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests de integración para el endpoint POST /reservations.
 *
 * Verifica el flujo completo: validación → disponibilidad → persistencia → evento.
 */
final class ReservationStoreTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;
    private Carbon $monday;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.reservation_slot_minutes' => 60]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->space  = Space::factory()->create(['is_active' => true]);
        $this->monday = Carbon::now()->next(Carbon::MONDAY);

        // Disponibilidad: lunes 08:00–18:00
        Availability::factory()->create([
            'space_id'    => $this->space->id,
            'day_of_week' => $this->monday->dayOfWeek,
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  Reserva exitosa
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function reserva_exitosa_crea_registro_y_dispara_evento(): void
    {
        Event::fake([ReservationCreated::class]);

        $payload = [
            'space_id'   => $this->space->id,
            'user_name'  => 'María García',
            'user_email' => 'maria@example.com',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ];

        $response = $this->post(route('reservations.store'), $payload);

        // Redirige a la confirmación
        $response->assertRedirect();

        // Reserva persistida en BD
        $this->assertDatabaseHas('reservations', [
            'space_id'   => $this->space->id,
            'user_email' => 'maria@example.com',
            'status'     => Reservation::STATUS_PENDIENTE,
        ]);

        // Evento disparado exactamente una vez
        Event::assertDispatched(ReservationCreated::class, 1);
    }

    // ══════════════════════════════════════════════════════════════
    //  Validación de email
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function email_invalido_retorna_error_422(): void
    {
        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Ana López',
            'user_email' => 'no-es-un-email',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['user_email']);
    }

    #[Test]
    public function email_vacio_retorna_error_validacion(): void
    {
        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Ana López',
            'user_email' => '',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['user_email']);
    }

    // ══════════════════════════════════════════════════════════════
    //  Validación de disponibilidad (colisión)
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function slot_ocupado_retorna_error_de_disponibilidad(): void
    {
        Event::fake();

        // Reserva previa que ocupa el slot
        Reservation::factory()->confirmada()->create([
            'space_id'   => $this->space->id,
            'start_time' => $this->monday->copy()->setTime(10, 0),
            'end_time'   => $this->monday->copy()->setTime(11, 0),
        ]);

        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Pedro Martínez',
            'user_email' => 'pedro@example.com',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['start_time']);
        Event::assertNotDispatched(ReservationCreated::class);
    }

    #[Test]
    public function slot_fuera_de_horario_retorna_error_de_disponibilidad(): void
    {
        Event::fake();

        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Luis Torres',
            'user_email' => 'luis@example.com',
            'start_time' => $this->monday->copy()->setTime(20, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['start_time']);
        Event::assertNotDispatched(ReservationCreated::class);
    }

    // ══════════════════════════════════════════════════════════════
    //  Validaciones de campos requeridos
    // ══════════════════════════════════════════════════════════════

    #[Test]
    public function campos_requeridos_retornan_errores(): void
    {
        $response = $this->post(route('reservations.store'), []);

        $response->assertSessionHasErrors([
            'space_id',
            'user_name',
            'user_email',
            'start_time',
            'duration',
        ]);
    }

    #[Test]
    public function sala_inactiva_no_acepta_reservas(): void
    {
        $spaceInactiva = Space::factory()->inactive()->create();

        $response = $this->post(route('reservations.store'), [
            'space_id'   => $spaceInactiva->id,
            'user_name'  => 'Carlos Ruiz',
            'user_email' => 'carlos@example.com',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['space_id']);
    }

    #[Test]
    public function duracion_minima_debe_cumplirse(): void
    {
        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Ana Gómez',
            'user_email' => 'ana@example.com',
            'start_time' => $this->monday->copy()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 15,
        ]);

        $response->assertSessionHasErrors(['duration']);
    }

    #[Test]
    public function no_se_puede_reservar_en_fecha_pasada(): void
    {
        $response = $this->post(route('reservations.store'), [
            'space_id'   => $this->space->id,
            'user_name'  => 'Test User',
            'user_email' => 'test@example.com',
            'start_time' => Carbon::yesterday()->setTime(10, 0)->format('Y-m-d H:i'),
            'duration'   => 60,
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }
}
