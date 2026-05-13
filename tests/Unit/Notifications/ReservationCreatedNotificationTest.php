<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Models\Reservation;
use App\Notifications\ReservationCreatedNotification;
use PHPUnit\Framework\TestCase;

final class ReservationCreatedNotificationTest extends TestCase
{
    private ReservationCreatedNotification $notification;

    protected function setUp(): void
    {
        parent::setUp();
        $reservation = new Reservation([
            'slug'       => 'test-slug',
            'user_name'  => 'Juan Pérez',
            'user_email' => 'juan@example.com',
        ]);
        $reservation->exists = true;
        $this->notification = new ReservationCreatedNotification($reservation);
    }

    public function test_via_returns_mail(): void
    {
        $this->assertEquals(['mail'], $this->notification->via(new \stdClass()));
    }
}
