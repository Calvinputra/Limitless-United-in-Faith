<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Validation\ValidationException;

class GerejaOptionService
{
    public const SETTING_KEY = 'gereja_options';

    /**
     * @return list<string>
     */
    public static function defaults(): array
    {
        return [
            'Central Park',
            'Puri',
            'Gancit',
            'Kelapa Gading',
            'Pluit',
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $raw = Setting::getValue(self::SETTING_KEY);

        if (! is_string($raw) || $raw === '') {
            return self::defaults();
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return self::defaults();
        }

        $items = [];

        foreach ($decoded as $row) {
            if (is_string($row)) {
                $name = trim($row);
            } elseif (is_array($row)) {
                // Backward compatible with old key/value format
                $name = trim((string) ($row['value'] ?? $row['key'] ?? ''));
            } else {
                continue;
            }

            if ($name === '') {
                continue;
            }

            $items[] = $name;
        }

        $items = array_values(array_unique($items));

        return $items !== [] ? $items : self::defaults();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public static function selectOptions(): array
    {
        return array_map(
            fn (string $name) => [
                'id' => $name,
                'name' => $name,
            ],
            self::all(),
        );
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return self::all();
    }

    public static function label(?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        return $key;
    }

    /**
     * @param  list<string|array{key?: string, value?: string}>  $items
     */
    public static function save(array $items): void
    {
        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $name = trim((string) ($item['value'] ?? $item['key'] ?? $item['name'] ?? ''));
            } else {
                $name = trim((string) $item);
            }

            if ($name === '') {
                continue;
            }

            $lookup = mb_strtolower($name);

            if (isset($seen[$lookup])) {
                throw ValidationException::withMessages([
                    'items' => "Gereja \"{$name}\" duplikat.",
                ]);
            }

            $seen[$lookup] = true;
            $normalized[] = $name;
        }

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu gereja lokal harus diisi.',
            ]);
        }

        Setting::setValue(self::SETTING_KEY, json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    public static function ensureSeeded(): void
    {
        if (Setting::getValue(self::SETTING_KEY) === null) {
            Setting::setValue(self::SETTING_KEY, json_encode(self::defaults(), JSON_UNESCAPED_UNICODE));
        }
    }
}
