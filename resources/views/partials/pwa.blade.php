@php
  use App\Support\BrandAssets;
  $pwaSettings = $settings ?? ($bmsSettings ?? []);
  $pwaTheme = $pwaSettings['theme'] ?? 'dark';
  $pwaHomeIcon = BrandAssets::pwaHomeIconUrl($pwaSettings);
  $pwaIconVersion = BrandAssets::pwaIconVersion($pwaSettings);
  $pwaAssetVersion = $pwaIconVersion;
@endphp
<link rel="manifest" href="{{ route('pwa.manifest') }}?v={{ $pwaAssetVersion }}">
<link rel="apple-touch-icon" href="{{ $pwaHomeIcon }}">
<meta name="pwa-icon-version" content="{{ $pwaIconVersion }}">
