<?php

namespace App\Support;

class BrandAssets
{
    /** Bump manually when you replace launcher PNGs to force reinstall prompts. */
    private const PWA_ICON_EPOCH = '20260527-q-v1';

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

    /** Home-screen / manifest PWA icon — bundled launcher PNGs, then favicon fallback. */
    public static function pwaHomeIconUrl(array $settings): string
    {
        if (self::hasLauncherIcons()) {
            return asset('pwa/icons/icon-192.png');
        }

        return self::faviconUrl($settings) ?? asset('pwa-icon.svg');
    }

    /** @return list<array{src: string, sizes: string, type: string, purpose: string}> */
    public static function pwaManifestIcons(array $settings): array
    {
        if (self::hasLauncherIcons()) {
            $icons = [];

            foreach ([
                ['file' => 'icon-192.png',  'sizes' => '192x192'],
                ['file' => 'icon-512.png',  'sizes' => '512x512'],
                ['file' => 'icon-1024.png', 'sizes' => '1024x1024'],
            ] as $def) {
                if (file_exists(public_path('pwa/icons/'.$def['file']))) {
                    $icons[] = [
                        'src'     => asset('pwa/icons/'.$def['file']),
                        'sizes'   => $def['sizes'],
                        'type'    => 'image/png',
                        'purpose' => 'any',
                    ];
                }
            }

            if (file_exists(public_path('pwa/icons/icon-512.png'))) {
                $icons[] = [
                    'src'     => asset('pwa/icons/icon-512.png'),
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ];
            }

            return $icons;
        }

        $fallback = self::faviconUrl($settings) ?? asset('pwa-icon.svg');

        return [[
            'src'     => $fallback,
            'sizes'   => 'any',
            'type'    => self::faviconMime($fallback),
            'purpose' => 'any maskable',
        ]];
    }

    public static function hasLauncherIcons(): bool
    {
        return file_exists(public_path('pwa/icons/icon-192.png'))
            && file_exists(public_path('pwa/icons/icon-512.png'));
    }

    /** Version string — changes when launcher files or favicon branding updates. */
    public static function pwaIconVersion(array $settings): string
    {
        $parts = [self::PWA_ICON_EPOCH];

        foreach (['icon-192.png', 'icon-512.png', 'icon-1024.png'] as $file) {
            $path = public_path('pwa/icons/'.$file);
            if (file_exists($path)) {
                $parts[] = $file.':'.filemtime($path).':'.filesize($path);
            }
        }

        if (! self::hasLauncherIcons()) {
            $parts[] = 'favicon:'.($settings['favicon_url'] ?? '');
        }

        return substr(md5(implode('|', $parts)), 0, 12);
    }

    /** Logo on in-app splash and login loader screens. */
    public static function pwaSplashLogoUrl(array $settings, string $theme = 'dark'): string
    {
        return self::logoForTheme($settings, $theme) ?? asset('pwa-icon.svg');
    }

    /** @deprecated Use pwaHomeIconUrl() or pwaSplashLogoUrl() explicitly. */
    public static function pwaIconUrl(array $settings, string $theme = 'dark'): string
    {
        return self::pwaHomeIconUrl($settings);
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
