<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }

    protected $fillable = [
        'space_id', 'user_name', 'user_email',
        'start_time', 'end_time', 'status', 'notes', 'slug',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public const STATUS_PENDIENTE  = 'pendiente';
    public const STATUS_CONFIRMADA = 'confirmada';
    public const STATUS_RECHAZADA  = 'rechazada';
    public const STATUS_CANCELADA  = 'cancelada';
    public const STATUS_FINALIZADA = 'finalizada';

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if (empty($reservation->slug)) {
                $reservation->slug = (string) Str::uuid();
            }
        });
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function scopePendiente($query)
    {
        return $query->where('status', self::STATUS_PENDIENTE);
    }

    public function scopeConfirmada($query)
    {
        return $query->where('status', self::STATUS_CONFIRMADA);
    }

    public function scopeOverlapping($query, string $start, string $end)
    {
        return $query->whereIn('status', [self::STATUS_PENDIENTE, self::STATUS_CONFIRMADA])
                     ->where('start_time', '<', $end)
                     ->where('end_time', '>', $start);
    }

    public function getDurationInHoursAttribute(): float
    {
        return $this->start_time->diffInMinutes($this->end_time) / 60;
    }
}
