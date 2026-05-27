@php
  use App\Services\BmsSettingsService;
  use App\Support\BmsSettingsDefaults;
  try {
    $cpSettings = app(BmsSettingsService::class)->all();
  } catch(\Throwable) {
    $cpSettings = BmsSettingsDefaults::all();
  }
  $cpCompany = $cpSettings['company_name'] ?? 'QuickPrints';
  $cpAccent  = $cpSettings['brand_color']  ?? '#b91c1c';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set Your Password — {{ $cpCompany }}</title>
@include('partials.favicon', ['settings' => $cpSettings])
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --accent:{{ $cpAccent }};
  --bg:#111318;--bg2:#1a1d24;--bg3:#222730;--border:#2e3340;
  --text:#e8eaf0;--text2:#9ba3b8;--text3:#5a6280;
  --red:#dc2626;--green:#16a34a;
  --font:'IBM Plex Sans',sans-serif;--mono:'IBM Plex Mono',monospace;
}
html,body{height:100%;font-family:var(--font);background:var(--bg);color:var(--text);}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}

.card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:36px 32px;width:100%;max-width:420px;box-shadow:0 8px 40px rgba(0,0,0,.35);}
.brand{text-align:center;margin-bottom:28px;}
.brand-icon{font-size:36px;margin-bottom:10px;}
.brand-name{font-size:16px;font-weight:700;color:var(--accent);letter-spacing:.04em;}
.brand-sub{font-size:12px;color:var(--text3);margin-top:4px;}

h2{font-size:20px;font-weight:700;margin-bottom:6px;}
.subtitle{font-size:13px;color:var(--text2);margin-bottom:24px;line-height:1.5;}

.alert{padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:16px;}
.alert-danger{background:rgba(220,38,38,.12);color:var(--red);border:1px solid rgba(220,38,38,.25);}

.fld{margin-bottom:16px;}
.fld label{display:block;font-size:11px;font-weight:600;color:var(--text2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.fld input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:6px;padding:9px 36px 9px 12px;color:var(--text);font-size:13px;transition:border-color .2s;}
.fld input:focus{border-color:var(--accent);outline:none;}
.pw-wrap{position:relative;}
.pw-eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:13px;line-height:1;padding:2px;}

.strength{height:3px;border-radius:99px;background:var(--border);overflow:hidden;margin-top:6px;}
.strength-bar{height:100%;width:0;border-radius:99px;transition:width .3s,background .3s;}
.strength-label{font-size:10px;margin-top:3px;color:var(--text3);}

.btn{display:block;width:100%;padding:11px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;border:none;background:var(--accent);color:#fff;transition:opacity .15s;margin-top:4px;}
.btn:hover{opacity:.88;}

.hint{font-size:11px;color:var(--text3);text-align:center;margin-top:16px;}
.hint a{color:var(--accent);text-decoration:none;}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-icon">🔐</div>
    <div class="brand-name">{{ strtoupper($cpCompany) }}</div>
    <div class="brand-sub">Business Management System</div>
  </div>

  <h2>Set Your Password</h2>
  <p class="subtitle">Your account was created by an administrator. Please choose a new password before continuing.</p>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul style="margin:0;padding-left:1.1rem;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('bms.password.change.update') }}">
    @csrf

    <div class="fld">
      <label>New Password <span style="color:var(--red)">*</span></label>
      <div class="pw-wrap">
        <input type="password" name="password" id="pw" autocomplete="new-password"
               minlength="8" required placeholder="Minimum 8 characters"
               oninput="checkStrength(this.value)">
        <button type="button" class="pw-eye" onclick="toggleVis('pw',this)">👁</button>
      </div>
      <div class="strength"><div class="strength-bar" id="pw-bar"></div></div>
      <div class="strength-label" id="pw-lbl"></div>
    </div>

    <div class="fld">
      <label>Confirm Password <span style="color:var(--red)">*</span></label>
      <div class="pw-wrap">
        <input type="password" name="password_confirmation" id="pw2"
               autocomplete="new-password" minlength="8" required placeholder="Repeat password">
        <button type="button" class="pw-eye" onclick="toggleVis('pw2',this)">👁</button>
      </div>
    </div>

    <button type="submit" class="btn">Set Password &amp; Continue →</button>
  </form>

  <p class="hint">
    Signed in as <strong>{{ auth()->user()?->name }}</strong> ·
    <a href="{{ route('bms.logout') }}" onclick="event.preventDefault();document.getElementById('lo').submit();">Sign Out</a>
    <form id="lo" method="POST" action="{{ route('bms.logout') }}" style="display:none;">@csrf</form>
  </p>
</div>

<script>
function toggleVis(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}
function checkStrength(v) {
  const bar = document.getElementById('pw-bar');
  const lbl = document.getElementById('pw-lbl');
  if (!v) { bar.style.width='0'; lbl.textContent=''; return; }
  let s = 0;
  if (v.length >= 8)  s++;
  if (v.length >= 12) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  const c = ['#ef4444','#ef4444','#f97316','#eab308','#22c55e'][s] || '#22c55e';
  const t = ['Too short','Weak','Fair','Good','Strong'][s] || 'Strong';
  bar.style.width = (s/5*100)+'%';
  bar.style.background = c;
  lbl.textContent = t;
  lbl.style.color = c;
}
</script>
</body>
</html>
