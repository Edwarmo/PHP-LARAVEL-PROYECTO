<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'capacity',
        'description', 'price_per_hour', 'is_active',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'is_active'      => 'boolean',
        'capacity'         => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::creating(function (Space $space) {
            if (empty($space->slug)) {
                $space->slug = Str::slug($space->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = $value ? 'true' : 'false';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
