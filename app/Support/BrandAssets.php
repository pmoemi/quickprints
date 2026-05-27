<?php

namespace App\Support;

class BrandAssets
{
    /** @return array{dark: ?string, light: ?string, fallback: ?string} */
    public static function logos(array $settings): array
    {
        $fallback = $settings['logo_url'] ?? null;

        return [
            'dark' => $settings['logo_url_dark'] ?? $fallback,
            'light' => $settings['logo_url_light'] ?? $fallback,
            'fallback' => $fallback,
        ];
    }

    public static function logoForTheme(array $settings, string $theme): ?string
    {
        $logos = self::logos($settings);
        $theme = $theme === 'light' ? 'light' : 'dark';

        return $logos[$theme] ?? $logos['fallback'];
    }

    public static function hasLogo(array $settings): bool
    {
        $logos = self::logos($settings);

        return ! empty($logos['dark']) || ! empty($logos['light']) || ! empty($logos['fallback']);
    }
}
