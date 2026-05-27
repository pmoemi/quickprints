<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@php
  use App\Support\BmsSettingsDefaults;
  try {
    $errSettings = app(\App\Services\BmsSettingsService::class)->all();
  } catch(\Throwable) {
    $errSettings = BmsSettingsDefaults::all();
  }
  $errCompany  = $errSettings['company_name'] ?? 'QuickPrints';
  $errAccent   = $errSettings['brand_color']  ?? '#b91c1c';
  $errTheme    = auth()->user()?->theme ?? $errSettings['theme'] ?? 'dark';
@endphp
<title>{{ $errCode }} — {{ $errCompany }}</title>
@include('partials.favicon', ['settings' => $errSettings])
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --accent:{{ $errAccent }};
  --bg:#111318;--bg2:#1a1d24;--bg3:#222730;--border:#2e3340;
  --text:#e8eaf0;--text2:#9ba3b8;--text3:#5a6280;
  --font:'IBM Plex Sans',sans-serif;--mono:'IBM Plex Mono',monospace;
}
@if($errTheme === 'light')
:root{--bg:#f0f2f5;--bg2:#ffffff;--bg3:#f5f6f8;--border:#e2e5ea;--text:#1a1d23;--text2:#5a6070;--text3:#9099a8;}
@endif
html,body{height:100%;font-family:var(--font);background:var(--bg);color:var(--text);}
body{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:24px;text-align:center;}
.err-code{font-family:var(--mono);font-size:96px;font-weight:700;color:var(--accent);line-height:1;letter-spacing:-4px;opacity:.15;position:absolute;top:50%;left:50%;transform:translate(-50%,-60%);pointer-events:none;user-select:none;}
.err-wrap{position:relative;max-width:480px;width:100%;}
.err-icon{font-size:52px;margin-bottom:16px;line-height:1;}
.err-title{font-size:24px;font-weight:700;color:var(--text);margin-bottom:8px;}
.err-msg{font-size:14px;color:var(--text2);line-height:1.6;margin-bottom:28px;}
.err-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;border:none;transition:all .15s;}
.btn-primary{background:var(--accent);color:#fff;}
.btn-primary:hover{opacity:.88;}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border);}
.btn-secondary:hover{border-color:var(--accent);}
.err-meta{margin-top:32px;padding-top:20px;border-top:1px solid var(--border);font-size:11px;color:var(--text3);font-family:var(--mono);}
</style>
</head>
<body>
<div class="err-wrap">
  <div class="err-code">{{ $errCode }}</div>
  <div class="err-icon">{{ $errIcon }}</div>
  <div class="err-title">{{ $errTitle }}</div>
  <div class="err-msg">{!! $errMessage !!}</div>
  <div class="err-actions">
    @yield('actions')
  </div>
  <div class="err-meta">{{ $errCompany }} · Error {{ $errCode }}</div>
</div>
</body>
</html>
