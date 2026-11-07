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
        'group',
        'type',
        'label',
        'description',
    ];

    /**
     * Get a setting value with fallback and memory/cache storage.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = Cache::remember("app_setting_{$key}", 3600, function () use ($key) {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : null;
            });

            return $value !== null ? $value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set/update a setting value and clear cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text', ?string $label = null, ?string $description = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group,
                'type' => $type,
                'label' => $label ?? ucfirst(str_replace('_', ' ', $key)),
                'description' => $description,
            ]
        );

        Cache::forget("app_setting_{$key}");

        return $setting;
    }

    /**
     * Get all settings grouped by their group name.
     */
    public static function getGrouped(): array
    {
        return self::all()->groupBy('group')->toArray();
    }
}
