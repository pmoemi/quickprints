@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

<div class="card">
  <div class="card-header"><div class="card-title">Brand Colors</div></div>
  <form method="POST" action="{{ route('bms.settings.branding.update') }}">
    @csrf @method('PUT')
    <div class="form-row cols-2">
      <div class="fld">
        <label>Primary Color</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" name="brand_color" id="brand_color" value="{{ old('brand_color', $settings['brand_color'] ?? '#b91c1c') }}" style="width:44px;height:36px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;flex-shrink:0;" oninput="document.getElementById('brand_color_text').value=this.value">
          <input type="text" id="brand_color_text" value="{{ old('brand_color', $settings['brand_color'] ?? '#b91c1c') }}" style="flex:1;" oninput="document.getElementById('brand_color').value=this.value" onchange="document.getElementById('brand_color').value=this.value">
        </div>
      </div>
      <div class="fld">
        <label>Secondary Color</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" name="brand_color_secondary" id="brand_color2" value="{{ old('brand_color_secondary', $settings['brand_color_secondary'] ?? '#16a34a') }}" style="width:44px;height:36px;border:1px solid var(--border);border-radius:var(--radius);background:none;cursor:pointer;flex-shrink:0;" oninput="document.getElementById('brand_color2_text').value=this.value">
          <input type="text" id="brand_color2_text" value="{{ old('brand_color_secondary', $settings['brand_color_secondary'] ?? '#16a34a') }}" style="flex:1;" oninput="document.getElementById('brand_color2').value=this.value" onchange="document.getElementById('brand_color2').value=this.value">
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Save Colors</button>
  </form>
</div>

<div class="grid-2" style="margin-top:16px;">
  @foreach([
    'dark' => ['label' => 'Dark Mode Logo', 'hint' => 'Shown on dark backgrounds (portal, BMS sidebar). Use a light-coloured logo.', 'url' => $settings['logo_url_dark'] ?? null, 'preview_bg' => '#13161d'],
    'light' => ['label' => 'Light Mode Logo', 'hint' => 'Shown on light backgrounds. Use a dark-coloured logo.', 'url' => $settings['logo_url_light'] ?? null, 'preview_bg' => '#ffffff'],
  ] as $variant => $meta)
  <div class="card">
    <div class="card-header"><div class="card-title">{{ $meta['label'] }}</div></div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">{{ $meta['hint'] }}</p>
    @if(!empty($meta['url']))
      <div style="margin-bottom:14px;padding:12px;background:{{ $meta['preview_bg'] }};border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
        <img src="{{ $meta['url'] }}" alt="{{ $meta['label'] }}" style="max-height:64px;max-width:180px;object-fit:contain;">
      </div>
    @else
      <div style="background:var(--bg3);border:1px dashed var(--border);border-radius:var(--radius);padding:20px;text-align:center;margin-bottom:14px;color:var(--text3);font-size:12px;">No {{ strtolower($meta['label']) }} uploaded</div>
    @endif
    <form method="POST" action="{{ route('bms.settings.branding.logo') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="variant" value="{{ $variant }}">
      <div class="form-row">
        <div class="fld">
          <label>Upload PNG / JPG / SVG</label>
          <input type="file" name="logo" accept="image/*" style="padding:6px;" required>
          @error('logo')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        </div>
      </div>
      <button type="submit" class="btn btn-secondary">Upload</button>
    </form>
  </div>
  @endforeach
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header"><div class="card-title">Fallback Logo (optional)</div></div>
  <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Used when a theme-specific logo is not set. Legacy uploads are stored here.</p>
  @if(!empty($settings['logo_url']))
    <div style="margin-bottom:14px;padding:12px;background:var(--bg3);border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
      <img src="{{ $settings['logo_url'] }}" alt="Fallback logo" style="max-height:64px;max-width:180px;object-fit:contain;">
    </div>
  @endif
  <form method="POST" action="{{ route('bms.settings.branding.logo') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="variant" value="default">
    <div class="form-row">
      <div class="fld">
        <label>Upload fallback logo</label>
        <input type="file" name="logo" accept="image/*" style="padding:6px;" required>
      </div>
    </div>
    <button type="submit" class="btn btn-secondary">Upload Fallback</button>
  </form>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header"><div class="card-title">Favicon</div></div>
  <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Shown in browser tabs — usually on a light background. Use a simple, high-contrast icon.</p>
  @if(!empty($settings['favicon_url']))
    <div style="margin-bottom:16px;padding:14px;background:#e8eaef;border-radius:var(--radius);border:1px solid #dde1e8;">
      <div style="display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #d1d5db;border-radius:8px 8px 0 0;padding:8px 14px 8px 10px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <img src="{{ \App\Support\BrandAssets::faviconUrl($settings) }}" alt="Favicon preview" style="width:16px;height:16px;object-fit:contain;flex-shrink:0;">
        <span style="font-size:12px;color:#374151;font-weight:500;white-space:nowrap;">{{ $settings['company_name'] ?? 'QuickPrints' }}</span>
      </div>
      <div style="font-size:11px;color:#6b7280;margin-top:8px;">Browser tab preview</div>
    </div>
  @else
    <div style="background:#f8f9fb;border:1px dashed #d1d5db;border-radius:var(--radius);padding:24px;text-align:center;margin-bottom:16px;color:var(--text3);font-size:13px;">No favicon uploaded</div>
  @endif
  <form method="POST" action="{{ route('bms.settings.branding.favicon') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-row">
      <div class="fld">
        <label>Upload Favicon <span style="font-size:11px;color:var(--text3);">PNG, ICO, SVG — max 512 KB</span></label>
        <input type="file" name="favicon" accept=".png,.ico,.jpg,.jpeg,.gif,.svg,image/*" style="padding:6px;">
        @error('favicon')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>
    <button type="submit" class="btn btn-secondary">Upload Favicon</button>
  </form>
</div>
@endsection
