<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GerejaOptionService
{
    public const SETTING_KEY = 'gereja_options';

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function defaults(): array
    {
        return [
            ['key' => 'Central Park', 'value' => 'Central Park'],
            ['key' => 'Puri', 'value' => 'Puri'],
            ['key' => 'Gancit', 'value' => 'Gancit'],
            ['key' => 'Kelapa Gading', 'value' => 'Kelapa Gading'],
            ['key' => 'Pluit', 'value' => 'Pluit'],
        ];
    }

    /**
     * @return list<array{key: string, value: string}>
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
            if (! is_array($row)) {
                continue;
            }

            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $items[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $items !== [] ? $items : self::defaults();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public static function selectOptions(): array
    {
        return array_map(
            fn (array $item) => [
                'id' => $item['key'],
                'name' => $item['value'],
            ],
            self::all(),
        );
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_map(fn (array $item) => $item['key'], self::all());
    }

    public static function label(?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        foreach (self::all() as $item) {
            if ($item['key'] === $key) {
                return $item['value'];
            }
        }

        return $key;
    }

    /**
     * @param  list<array{key?: string, value?: string}>  $items
     */
    public static function save(array $items): void
    {
        $normalized = [];
        $seen = [];

        foreach ($items as $index => $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $value = trim((string) ($item['value'] ?? ''));

            if ($key === '' && $value === '') {
                continue;
            }

            if ($key === '') {
                $key = Str::slug($value) ?: 'gereja-'.($index + 1);
            }

            if ($value === '') {
                throw ValidationException::withMessages([
                    'items' => 'Value gereja tidak boleh kosong.',
                ]);
            }

            $lookup = Str::lower($key);

            if (isset($seen[$lookup])) {
                throw ValidationException::withMessages([
                    'items' => "Key \"{$key}\" duplikat. Gunakan key unik.",
                ]);
            }

            $seen[$lookup] = true;
            $normalized[] = [
                'key' => $key,
                'value' => $value,
            ];
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
