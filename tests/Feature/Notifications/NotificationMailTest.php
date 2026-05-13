<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Reservation;
use App\Models\Space;
use App\Notifications\ReservationCreatedNotification;
use App\Notifications\ReservationStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class NotificationMailTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;
    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->space = Space::factory()->create(['name' => 'Sala Test']);
        $this->reservation = Reservation::factory()->create([
            'space_id'  => $this->space->id,
            'user_name' => 'Juan Pérez',
        ]);
    }

    #[Test]
    public function reservation_created_notification_builds_mail(): void
    {
        $notification = new ReservationCreatedNotification($this->reservation);
        $mail = $notification->toMail($this->reservation);
        $this->assertStringContainsString('Sala Test', $mail->subject);
        $this->assertStringContainsString('recibida', $mail->subject);
    }

    #[Test]
    public function status_changed_confirmed_builds_mail(): void
    {
        $this->reservation->update(['status' => Reservation::STATUS_CONFIRMADA]);
        $notification = new ReservationStatusChangedNotification($this->reservation, Reservation::STATUS_PENDIENTE);
        $mail = $notification->toMail($this->reservation);
        $this->assertStringContainsString('confirmada', $mail->subject);
        $this->assertStringContainsString('Sala Test', $mail->subject);
    }

    #[Test]
    public function status_changed_rejected_builds_mail(): void
    {
        $this->reservation->update([
            'status' => Reservation::STATUS_RECHAZADA,
            'notes'  => '[Rechazo]: Sala en mantenimiento',
        ]);
        $notification = new ReservationStatusChangedNotification($this->reservation, Reservation::STATUS_PENDIENTE);
        $mail = $notification->toMail($this->reservation);
        $this->assertStringContainsString('no pudo ser procesada', $mail->subject);
    }

    #[Test]
    public function status_changed_cancelled_builds_mail(): void
    {
        $this->reservation->update([
            'status' => Reservation::STATUS_CANCELADA,
            'notes'  => '[Cancelación]: Solicitud del usuario',
        ]);
        $notification = new ReservationStatusChangedNotification($this->reservation, Reservation::STATUS_CONFIRMADA);
        $mail = $notification->toMail($this->reservation);
        $this->assertStringContainsString('cancelada', $mail->subject);
    }

    #[Test]
    public function status_changed_default_for_unknown_status(): void
    {
        $this->reservation->update(['status' => Reservation::STATUS_FINALIZADA]);
        $notification = new ReservationStatusChangedNotification($this->reservation, Reservation::STATUS_CONFIRMADA);
        $mail = $notification->toMail($this->reservation);
        $this->assertStringContainsString('Actualización', $mail->subject);
    }
}
