<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Application\Mail\ReservationStatusChangedMail;
use App\Events\ReservationStatusChanged;
use App\Listeners\SendReservationStatusChangedEmail;
use App\Domain\Models\Reservation;
use App\Domain\Models\Space;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SendReservationStatusChangedEmailTest extends TestCase
{
    private Space $space;
    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->space = new Space(['id' => 1, 'name' => 'Sala Test', 'slug' => 'sala-test']);
        $this->space->exists = true;
        $this->reservation = new Reservation([
            'slug'        => 'test-slug',
            'user_name'   => 'Juan Pérez',
            'user_email'  => 'juan@example.com',
            'start_time'  => '2026-06-01 10:00:00',
            'end_time'    => '2026-06-01 11:00:00',
            'status'      => Reservation::STATUS_CONFIRMADA,
        ]);
        $this->reservation->exists = true;
        $this->reservation->setRelation('space', $this->space);
    }

    #[Test]
    public function handle_sends_notification_for_confirmada(): void
    {
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_PENDIENTE);
        $listener = new SendReservationStatusChangedEmail();
        $listener->handle($event);
        Mail::assertQueued(ReservationStatusChangedMail::class, function ($mail) {
            return $mail->hasTo('juan@example.com');
        });
    }

    #[Test]
    public function handle_sends_notification_for_rechazada(): void
    {
        $this->reservation->status = Reservation::STATUS_RECHAZADA;
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_PENDIENTE);
        $listener = new SendReservationStatusChangedEmail();
        $listener->handle($event);
        Mail::assertQueued(ReservationStatusChangedMail::class, function ($mail) {
            return $mail->hasTo('juan@example.com');
        });
    }

    #[Test]
    public function handle_sends_notification_for_cancelada(): void
    {
        $this->reservation->status = Reservation::STATUS_CANCELADA;
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_CONFIRMADA);
        $listener = new SendReservationStatusChangedEmail();
        $listener->handle($event);
        Mail::assertQueued(ReservationStatusChangedMail::class, function ($mail) {
            return $mail->hasTo('juan@example.com');
        });
    }

    #[Test]
    public function handle_does_not_send_for_pendiente(): void
    {
        $this->reservation->status = Reservation::STATUS_PENDIENTE;
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_CONFIRMADA);
        $listener = new SendReservationStatusChangedEmail();
        $listener->handle($event);
        Mail::assertNothingQueued();
    }

    #[Test]
    public function handle_does_not_send_for_finalizada(): void
    {
        $this->reservation->status = Reservation::STATUS_FINALIZADA;
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_CONFIRMADA);
        $listener = new SendReservationStatusChangedEmail();
        $listener->handle($event);
        Mail::assertNothingQueued();
    }

    #[Test]
    public function failed_logs_error(): void
    {
        Log::shouldReceive('error')->once()->withArgs(function ($message, $context) {
            return str_contains($message, 'Error enviando email de cambio de estado de reserva');
        });
        $event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_PENDIENTE);
        $listener = new SendReservationStatusChangedEmail();
        $listener->failed($event, new \RuntimeException('SMTP error'));
    }
}
