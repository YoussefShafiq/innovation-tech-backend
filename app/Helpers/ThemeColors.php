<?php

namespace App\Helpers;

class ThemeColors
{
    public const KEYS = [
        'primary',
        'primary_light',
        'primary_dark',
        'secondary',
        'secondary_light',
        'secondary_dark',
        'background',
        'surface',
        'text_primary',
        'text_secondary',
        'dark_surface',
    ];

    public static function defaults(): array
    {
        return [
            'primary' => '#F7941D',
            'primary_light' => '#FBB55A',
            'primary_dark' => '#D97706',
            'secondary' => '#2B3990',
            'secondary_light' => '#4A5BC4',
            'secondary_dark' => '#1A2460',
            'background' => '#F8FAFC',
            'surface' => '#FFFFFF',
            'text_primary' => '#0F172A',
            'text_secondary' => '#64748B',
            'dark_surface' => '#060B1F',
        ];
    }

    /**
     * Expand #RGB to #RRGGBB and uppercase. Returns null if invalid.
     */
    public static function normalizeHex(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        if (!preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $value)) {
            return null;
        }

        $hex = strtoupper(substr($value, 1));
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return '#' . $hex;
    }

    /**
     * Merge stored colors over defaults. Invalid/missing keys fall back to defaults.
     */
    public static function normalize(mixed $stored): array
    {
        $defaults = self::defaults();
        $incoming = is_array($stored) ? $stored : [];
        $result = [];

        foreach (self::KEYS as $key) {
            $normalized = self::normalizeHex(
                isset($incoming[$key]) && is_string($incoming[$key]) ? $incoming[$key] : null
            );
            $result[$key] = $normalized ?? $defaults[$key];
        }

        return $result;
    }

    /**
     * Validate and normalize a full theme payload for storage.
     * Returns ['ok' => true, 'theme' => [...]] or ['ok' => false, 'message' => '...'].
     */
    public static function validatePayload(array $input): array
    {
        $theme = [];

        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $input) || !is_string($input[$key]) || trim($input[$key]) === '') {
                return [
                    'ok' => false,
                    'message' => "The {$key} color is required.",
                ];
            }

            $normalized = self::normalizeHex($input[$key]);
            if ($normalized === null) {
                return [
                    'ok' => false,
                    'message' => "The {$key} color must be a valid hex color (#RGB or #RRGGBB).",
                ];
            }

            $theme[$key] = $normalized;
        }

        return ['ok' => true, 'theme' => $theme];
    }
}
