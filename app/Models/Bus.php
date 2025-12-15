<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'departure_time',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    // Relationships
    public function assignments()
    {
        return $this->hasMany(BusAssignment::class);
    }

    public function participants()
    {
        return $this->hasManyThrough(
            Participant::class,
            BusAssignment::class,
            'bus_id',
            'id',
            'id',
            'participant_id'
        );
    }

    // Helpers
    public function getOccupiedSeatsAttribute(): int
    {
        return $this->assignments()->count();
    }

    public function getRemainingCapacityAttribute(): int
    {
        return $this->capacity - $this->occupied_seats;
    }

    public function isFull(): bool
    {
        return $this->occupied_seats >= $this->capacity;
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity == 0) return 0;
        return round(($this->occupied_seats / $this->capacity) * 100, 1);
    }

    public function getStatusClassAttribute(): string
    {
        $percentage = $this->occupancy_percentage;
        if ($percentage >= 100) return 'bg-red-500';
        if ($percentage >= 80) return 'bg-yellow-500';
        return 'bg-green-500';
    }

    // Scopes
    public function scopeMorning($query)
    {
        return $query->where('departure_time', '06:00');
    }

    public function scopePriority($query)
    {
        return $query->where('departure_time', '06:30');
    }

    public function scopeRegular($query)
    {
        return $query->where('departure_time', '07:00');
    }

    public function getDepartureTimeLabelAttribute(): string
    {
        return match($this->departure_time) {
            '06:00' => 'Pagi (06:00) - PERFORM',
            '06:30' => 'Prioritas (06:30) - KIDDIES',
            '07:00' => 'Reguler (07:00) - UMUM',
            default => $this->departure_time,
        };
    }
}
