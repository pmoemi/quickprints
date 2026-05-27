@php
  use App\Support\BrandAssets;
  $pwaSettings = $settings ?? ($bmsSettings ?? []);
  $pwaTheme = $pwaSettings['theme'] ?? 'dark';
  $pwaIcon = BrandAssets::pwaIconUrl($pwaSettings, $pwaTheme);
@endphp
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="apple-touch-icon" href="{{ $pwaIcon }}">
