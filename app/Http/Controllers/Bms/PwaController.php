<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Services\BmsSettingsService;
use App\Support\BrandAssets;
use App\Support\BrandColors;
use Illuminate\Http\JsonResponse;

class PwaController extends Controller
{
    public function manifest(BmsSettingsService $settings): JsonResponse
    {
        $all     = $settings->all();
        $brand   = BrandColors::fromSettings($all);
        $company = trim((string) ($all['company_name'] ?? 'QuickPrints')) ?: 'QuickPrints';

        return response()->json([
            'name'             => $company.' BMS',
            'short_name'       => $company,
            'description'      => $company.' Business Management System',
            'start_url'        => url('/'),
            'scope'            => url('/'),
            'display'          => 'standalone',
            'background_color' => '#111318',
            'theme_color'      => $brand['primary'],
            'orientation'      => 'portrait',
            'icon_version'     => BrandAssets::pwaIconVersion($all),
            'icons'            => BrandAssets::pwaManifestIcons($all),
        ], 200, [
            'Content-Type'              => 'application/manifest+json; charset=utf-8',
            'Cache-Control'             => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
