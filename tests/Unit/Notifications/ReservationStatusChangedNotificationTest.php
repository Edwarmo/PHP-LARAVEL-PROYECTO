<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\Reservation;
use App\Notifications\ReservationStatusChangedNotification;
use PHPUnit\Framework\TestCase;

final class ReservationStatusChangedNotificationTest extends TestCase
{
    private ReservationStatusChangedNotification $notification;

    protected function setUp(): void
    {
        parent::setUp();
        $reservation = new Reservation([
            'slug'       => 'test-slug',
            'user_name'  => 'Juan Pérez',
            'user_email' => 'juan@example.com',
            'status'     => Reservation::STATUS_CONFIRMADA,
        ]);
        $reservation->exists = true;
        $this->notification = new ReservationStatusChangedNotification(
            $reservation,
            Reservation::STATUS_PENDIENTE
        );
    }

    public function test_via_returns_mail(): void
    {
        $this->assertEquals(['mail'], $this->notification->via(new \stdClass()));
    }
}
