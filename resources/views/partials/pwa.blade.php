@php
  use App\Support\BrandAssets;
  $pwaSettings = $settings ?? ($bmsSettings ?? []);
  $pwaTheme = $pwaSettings['theme'] ?? 'dark';
  $pwaHomeIcon = BrandAssets::pwaHomeIconUrl($pwaSettings);
  $pwaAssetVersion = substr(md5($pwaHomeIcon . BrandAssets::pwaSplashLogoUrl($pwaSettings, $pwaTheme)), 0, 8);
@endphp
<link rel="manifest" href="{{ route('pwa.manifest') }}?v={{ $pwaAssetVersion }}">
<link rel="apple-touch-icon" href="{{ $pwaHomeIcon }}">
