<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'ip_address',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity
     */
    public static function log(string $action, string $description, $model = null): self
    {
        return static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? class_basename($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get action badge class for display
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match($this->action) {
            'create' => 'bg-emerald-500',
            'update' => 'bg-blue-500',
            'delete' => 'bg-red-500',
            'login' => 'bg-purple-500',
            'logout' => 'bg-gray-500',
            'auto_assign' => 'bg-indigo-500',
            'reset' => 'bg-amber-500',
            default => 'bg-gray-400',
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            'login' => 'Login',
            'logout' => 'Logout',
            'auto_assign' => 'Auto Assign',
            'reset' => 'Reset',
            'import' => 'Import',
            default => ucfirst($this->action),
        };
    }
}
