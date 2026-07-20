<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function get(string $key, $default = null)
    {
        return static::all()->firstWhere('key', $key)?->value ?? $default;
    }

    public static function allCached(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->pluck('value', 'key');
        });
    }

    public static function value(string $key, $default = null)
    {
        return static::allCached()->get($key) ?? $default;
    }

    public static function put(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        static::updateOrCreate(['key' => $key], compact('value', 'group', 'type'));
        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }
}
