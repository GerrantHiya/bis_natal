<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("setting_{$key}");
    }

    /**
     * Get default bus capacity
     */
    public static function getDefaultBusCapacity(): int
    {
        return (int) static::get('default_bus_capacity', 40);
    }

    /**
     * Check if PERFORM guardians should be separated
     */
    public static function shouldSeparatePerformGuardians(): bool
    {
        return (bool) static::get('separate_perform_guardians', false);
    }
}
