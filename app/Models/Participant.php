<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'age',
        'guardian_name',
        'category',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    // Relationships
    public function busAssignment()
    {
        return $this->hasOne(BusAssignment::class)->where('is_guardian', false);
    }

    public function guardianAssignment()
    {
        return $this->hasOne(BusAssignment::class)->where('is_guardian', true);
    }

    public function bus()
    {
        return $this->hasOneThrough(
            Bus::class,
            BusAssignment::class,
            'participant_id',
            'id',
            'id',
            'bus_id'
        );
    }

    // Scopes
    public function scopePerform($query)
    {
        return $query->where('category', 'perform');
    }

    public function scopeUmum($query)
    {
        return $query->where('category', 'umum');
    }

    public function scopePribadi($query)
    {
        return $query->where('category', 'pribadi');
    }

    public function scopeEligibleForBus($query)
    {
        return $query->whereIn('category', ['perform', 'umum']);
    }

    // Helpers
    public function isPerform(): bool
    {
        return $this->category === 'perform';
    }

    public function isUmum(): bool
    {
        return $this->category === 'umum';
    }

    public function isPribadi(): bool
    {
        return $this->category === 'pribadi';
    }

    public function hasGuardian(): bool
    {
        return !empty($this->guardian_name);
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match($this->category) {
            'perform' => 'bg-green-500',
            'umum' => 'bg-blue-500',
            'pribadi' => 'bg-gray-500',
            default => 'bg-gray-400',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return strtoupper($this->category);
    }
}
