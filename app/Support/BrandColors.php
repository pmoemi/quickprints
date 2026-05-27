<?php

namespace App\Support;

class BrandColors
{
    /** @return array{primary: string, secondary: string, rgb: string} */
    public static function fromSettings(array $settings): array
    {
        $primary = self::normalize($settings['brand_color'] ?? '#b91c1c');
        $secondary = self::normalize($settings['brand_color_secondary'] ?? '#dc2626');

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'rgb' => self::toRgbString($primary),
        ];
    }

    public static function normalize(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if ($hex === '') {
            return '#b91c1c';
        }
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.strtolower(substr($hex, 0, 6));
    }

    public static function toRgbString(string $hex): string
    {
        $hex = ltrim(self::normalize($hex), '#');

        return sprintf(
            '%d, %d, %d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    public static function rgba(string $hex, float $alpha): string
    {
        return 'rgba('.self::toRgbString($hex).', '.$alpha.')';
    }
}
