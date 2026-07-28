<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting.{$key}", 60, function () use ($key, $default) {
            $setting = static::query()->find($key);

            return $setting?->value ?? $default;
        });
    }

    public static function setValue(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget("setting.{$key}");
    }

    public static function teamCount(): int
    {
        return max(1, (int) static::getValue('team_count', '4'));
    }
}
