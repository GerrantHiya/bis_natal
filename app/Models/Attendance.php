<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_assignment_id',
        'type',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    // Constants
    const TYPE_BERANGKAT = 'berangkat';
    const TYPE_PULANG = 'pulang';

    // Relationships
    public function busAssignment()
    {
        return $this->belongsTo(BusAssignment::class);
    }

    public function checkedByUser()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    // Helpers
    public function isBerangkat(): bool
    {
        return $this->type === self::TYPE_BERANGKAT;
    }

    public function isPulang(): bool
    {
        return $this->type === self::TYPE_PULANG;
    }
}
