<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $settings['company_name'] ?? 'QuickPrints' }} — Reset Password</title>
@include('partials.favicon')
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#111318;--bg2:#1a1d24;--bg3:#222730;--border:#2e3340;--border2:#3a4050;--text:#e8eaf0;--text2:#9ba3b8;--text3:#5a6280;--accent:#b91c1c;--accent2:#dc2626;--red:#dc2626;--red-dim:rgba(220,38,38,.14);--radius:6px;--font:'IBM Plex Sans','Helvetica Neue',Arial,sans-serif;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--font);background:linear-gradient(135deg,#1e2227 0%,#2d333b 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;color:var(--text);}
.box{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:40px;width:400px;max-width:90vw;box-shadow:0 24px 80px rgba(0,0,0,.35);}
.logo{text-align:center;margin-bottom:28px;}
.logo h1{font-size:22px;font-weight:700;color:var(--accent);letter-spacing:1px;}
.logo p{font-size:12px;color:var(--text3);margin-top:4px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:11px;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:10px 12px;color:var(--text);font-size:14px;font-family:var(--font);transition:border-color .2s;}
.form-group input:focus{border-color:var(--accent);outline:none;}
.password-wrap{position:relative;}
.password-wrap input{padding-right:42px;}
.password-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:var(--bg2);color:var(--text2);display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;}
.btn-submit{width:100%;background:var(--accent);color:#fff;font-weight:600;font-size:14px;padding:11px;border-radius:var(--radius);margin-top:8px;border:none;cursor:pointer;font-family:var(--font);transition:background .2s;}
.btn-submit:hover{background:var(--accent2);}
.alert-err{color:var(--red);background:var(--red-dim);font-size:12px;text-align:center;padding:10px;border-radius:var(--radius);margin-bottom:16px;}
.back{display:block;text-align:center;margin-top:20px;font-size:13px;color:var(--text3);text-decoration:none;}
.hint{font-size:12px;color:var(--text3);margin:0 0 6px;display:block;}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <h1>{{ strtoupper($settings['company_name'] ?? 'QUICK PRINTS') }}</h1>
    <p>Set New Password</p>
  </div>

  @if($errors->any())
    <div class="alert-err">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('bms.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
             placeholder="you@company.com" required autocomplete="email">
    </div>

    <div class="form-group">
      <label for="password">New Password</label>
      <span class="hint">Minimum 8 characters</span>
      <div class="password-wrap">
        <input type="password" id="password" name="password"
               placeholder="••••••••" required autocomplete="new-password" minlength="8">
        <button type="button" class="password-toggle" onclick="togglePwd('password',this)">👁</button>
      </div>
    </div>

    <div class="form-group">
      <label for="password_confirmation">Confirm Password</label>
      <div class="password-wrap">
        <input type="password" id="password_confirmation" name="password_confirmation"
               placeholder="••••••••" required autocomplete="new-password">
        <button type="button" class="password-toggle" onclick="togglePwd('password_confirmation',this)">👁</button>
      </div>
    </div>

    <button type="submit" class="btn-submit">Reset Password →</button>
  </form>

  <a href="{{ route('bms.login') }}" class="back">← Back to Sign In</a>
</div>
<script>
function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.textContent = show ? '🙈' : '👁';
}
</script>
</body>
</html>
