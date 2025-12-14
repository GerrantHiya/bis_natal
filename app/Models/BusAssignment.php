<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'participant_id',
        'is_guardian',
        'linked_participant_id',
    ];

    protected $casts = [
        'is_guardian' => 'boolean',
    ];

    // Relationships
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function linkedParticipant()
    {
        return $this->belongsTo(Participant::class, 'linked_participant_id');
    }

    // Helpers
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_guardian) {
            return $this->linkedParticipant?->guardian_name ?? 'Pendamping';
        }
        return $this->participant->name;
    }

    public function getTypeAttribute(): string
    {
        return $this->is_guardian ? 'Pendamping' : 'Anak';
    }
}
