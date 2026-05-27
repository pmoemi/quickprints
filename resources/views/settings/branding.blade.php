@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

<div class="grid-2">
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

  <div class="card">
    <div class="card-header"><div class="card-title">Company Logo</div></div>
    @if(!empty($settings['logo_url']))
      <div style="margin-bottom:16px;padding:12px;background:var(--bg3);border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
        <img src="{{ $settings['logo_url'] }}" alt="Logo" style="max-height:80px;max-width:200px;">
      </div>
    @else
      <div style="background:var(--bg3);border:1px dashed var(--border);border-radius:var(--radius);padding:24px;text-align:center;margin-bottom:16px;color:var(--text3);font-size:13px;">No logo uploaded</div>
    @endif
    <form method="POST" action="{{ route('bms.settings.branding.logo') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-row">
        <div class="fld">
          <label>Upload New Logo</label>
          <input type="file" name="logo" accept="image/*" style="padding:6px;">
          @error('logo')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        </div>
      </div>
      <button type="submit" class="btn btn-secondary">Upload Logo</button>
    </form>
  </div>
</div>

<div class="grid-2" style="margin-top:0;">
  <div class="card">
    <div class="card-header"><div class="card-title">Favicon</div></div>
    @if(!empty($settings['favicon_url']))
      <div style="margin-bottom:16px;padding:12px;background:var(--bg3);border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;gap:12px;">
        <img src="{{ $settings['favicon_url'] }}" alt="Favicon" style="width:32px;height:32px;object-fit:contain;">
        <span style="font-size:12px;color:var(--text3);">Current favicon (shown in browser tab)</span>
      </div>
    @else
      <div style="background:var(--bg3);border:1px dashed var(--border);border-radius:var(--radius);padding:24px;text-align:center;margin-bottom:16px;color:var(--text3);font-size:13px;">No favicon uploaded</div>
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
</div>
@endsection
