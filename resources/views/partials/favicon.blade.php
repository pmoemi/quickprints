@php
  use App\Support\BrandAssets;
  $faviconSettings = $settings ?? ($bmsSettings ?? []);
  $faviconUrl = BrandAssets::faviconUrl($faviconSettings);
@endphp
@if($faviconUrl)
  <link rel="icon" href="{{ $faviconUrl }}" type="{{ BrandAssets::faviconMime($faviconUrl) }}" sizes="any">
  <link rel="shortcut icon" href="{{ $faviconUrl }}">
@endif
