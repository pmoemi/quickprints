<?php

namespace App\Support;

class BrandAssets
{
    /** Resolve branding file URLs against the current app base (works in subfolders). */
    public static function publicUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('#/storage/(.+)$#', $url, $m)) {
            return asset('storage/'.$m[1]);
        }

        if (str_starts_with($url, '//')) {
            return (request()->isSecure() ? 'https:' : 'http:').$url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return asset(ltrim($url, '/'));
    }

    public static function storagePathUrl(string $relativePath): string
    {
        return asset('storage/'.ltrim($relativePath, '/'));
    }

    public static function faviconUrl(array $settings): ?string
    {
        return self::publicUrl($settings['favicon_url'] ?? null);
    }

    public static function faviconMime(?string $url): string
    {
        $path = parse_url($url ?? '', PHP_URL_PATH) ?: '';

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
    }

    /** @return array{dark: ?string, light: ?string, fallback: ?string} */
    public static function logos(array $settings): array
    {
        $fallback = self::publicUrl($settings['logo_url'] ?? null);

        return [
            'dark' => self::publicUrl($settings['logo_url_dark'] ?? null) ?? $fallback,
            'light' => self::publicUrl($settings['logo_url_light'] ?? null) ?? $fallback,
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
