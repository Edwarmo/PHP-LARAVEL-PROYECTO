<?php

declare(strict_types=1);

namespace Tests\Unit\Events;

use App\Events\ReservationStatusChanged;
use App\Domain\Models\Reservation;
use PHPUnit\Framework\TestCase;

final class ReservationStatusChangedTest extends TestCase
{
    private Reservation $reservation;
    private ReservationStatusChanged $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reservation = new Reservation(['status' => Reservation::STATUS_CONFIRMADA]);
        $this->event = new ReservationStatusChanged($this->reservation, Reservation::STATUS_PENDIENTE);
    }

    public function test_constructor_sets_reservation(): void
    {
        $this->assertSame($this->reservation, $this->event->reservation);
    }

    public function test_constructor_sets_previous_status(): void
    {
        $this->assertSame(Reservation::STATUS_PENDIENTE, $this->event->previousStatus);
    }

    public function test_was_confirmed_returns_true_when_pendiente_to_confirmada(): void
    {
        $event = new ReservationStatusChanged(
            new Reservation(['status' => Reservation::STATUS_CONFIRMADA]),
            Reservation::STATUS_PENDIENTE
        );
        $this->assertTrue($event->wasConfirmed());
    }

    public function test_was_confirmed_returns_false_when_not_from_pendiente(): void
    {
        $event = new ReservationStatusChanged(
            new Reservation(['status' => Reservation::STATUS_CONFIRMADA]),
            Reservation::STATUS_RECHAZADA
        );
        $this->assertFalse($event->wasConfirmed());
    }

    public function test_was_rejected_returns_true_when_status_is_rechazada(): void
    {
        $event = new ReservationStatusChanged(
            new Reservation(['status' => Reservation::STATUS_RECHAZADA]),
            Reservation::STATUS_PENDIENTE
        );
        $this->assertTrue($event->wasRejected());
    }

    public function test_was_rejected_returns_false_when_status_is_not_rechazada(): void
    {
        $this->assertFalse($this->event->wasRejected());
    }

    public function test_was_cancelled_returns_true_when_status_is_cancelada(): void
    {
        $event = new ReservationStatusChanged(
            new Reservation(['status' => Reservation::STATUS_CANCELADA]),
            Reservation::STATUS_CONFIRMADA
        );
        $this->assertTrue($event->wasCancelled());
    }

    public function test_was_cancelled_returns_false_when_status_is_not_cancelada(): void
    {
        $this->assertFalse($this->event->wasCancelled());
    }
}
