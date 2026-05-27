<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class BmsFrontendInstaller
{
    public const SOURCE = 'QuickPrints_BMS_Offline.html';

    public static function sourcePath(): string
    {
        return base_path('QuickPrints_BMS_Offline.html');
    }

    public static function targetPath(): string
    {
        return public_path('bms/index.html');
    }

    public static function isInstalled(): bool
    {
        return File::exists(self::targetPath());
    }

    public static function sourceExists(): bool
    {
        return File::exists(self::sourcePath());
    }

    public static function install(): bool
    {
        if (! self::sourceExists()) {
            return false;
        }

        File::ensureDirectoryExists(dirname(self::targetPath()));

        $html = File::get(self::sourcePath());
        $html = self::patchApiBase($html);

        File::put(self::targetPath(), $html);

        return true;
    }

    private static function patchApiBase(string $html): string
    {
        $dynamicBase = <<<'JS'
base: (() => {
    const path = window.location.pathname.replace(/\/+$/, '');
    const root = path.endsWith('/bms') ? path : path.replace(/\/bms\/.*$/, '/bms');
    return root + '/api';
  })(),
JS;

        $html = preg_replace(
            "/base:\s*'\/bms\/api',/",
            $dynamicBase,
            $html,
            1
        ) ?? $html;

        return str_replace(
            "fetch('/bms/api/auth/login'",
            "fetch(API.base + '/auth/login'",
            $html
        );
    }
}
