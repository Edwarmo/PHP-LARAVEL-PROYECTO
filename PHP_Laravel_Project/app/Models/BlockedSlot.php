<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedSlot extends Model
{
    use HasFactory;

    protected $fillable = ['space_id', 'start_time', 'end_time', 'reason'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function scopeOverlapping($query, string $start, string $end)
    {
        return $query->where('start_time', '<', $end)
                     ->where('end_time', '>', $start);
    }
}
