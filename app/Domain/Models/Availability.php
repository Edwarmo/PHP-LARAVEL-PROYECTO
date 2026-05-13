<?php

declare(strict_types=1);

namespace App\Domain\Models;

use Database\Factories\AvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected static function newFactory(): AvailabilityFactory
    {
        return AvailabilityFactory::new();
    }

    protected $fillable = ['space_id', 'day_of_week', 'start_time', 'end_time'];

    protected $casts = ['day_of_week' => 'integer'];

    public const DAYS = [
        0 => 'Domingo', 1 => 'Lunes',  2 => 'Martes',
        3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Desconocido';
    }
}
