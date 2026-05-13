<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Events\ReservationCreated;
use App\Listeners\SendReservationConfirmationEmail;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SendReservationConfirmationEmailTest extends TestCase
{
    private Space $space;
    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->space = new Space(['id' => 1, 'name' => 'Sala Test', 'slug' => 'sala-test']);
        $this->space->exists = true;
        $this->reservation = new Reservation([
            'slug'        => 'test-slug',
            'user_name'   => 'Juan Pérez',
            'user_email'  => 'juan@example.com',
            'start_time'  => '2026-06-01 10:00:00',
            'end_time'    => '2026-06-01 11:00:00',
            'status'      => Reservation::STATUS_PENDIENTE,
        ]);
        $this->reservation->exists = true;
        $this->reservation->setRelation('space', $this->space);
    }

    #[Test]
    public function handle_sends_notification_via_mail(): void
    {
        $event = new ReservationCreated($this->reservation);
        $listener = new SendReservationConfirmationEmail();
        $listener->handle($event);
        Notification::assertSentTo(
            Notification::route('mail', ['juan@example.com' => 'Juan Pérez']),
            \App\Notifications\ReservationCreatedNotification::class
        );
    }

    #[Test]
    public function failed_logs_error(): void
    {
        Log::shouldReceive('error')->once()->withArgs(function ($message, $context) {
            return str_contains($message, 'Error enviando email de creación de reserva');
        });
        $event = new ReservationCreated($this->reservation);
        $listener = new SendReservationConfirmationEmail();
        $listener->failed($event, new \RuntimeException('SMTP error'));
    }
}
