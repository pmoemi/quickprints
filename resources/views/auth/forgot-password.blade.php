<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $settings['company_name'] ?? 'QuickPrints' }} — Forgot Password</title>
@include('partials.favicon')
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#111318;--bg2:#1a1d24;--bg3:#222730;--border:#2e3340;--border2:#3a4050;--text:#e8eaf0;--text2:#9ba3b8;--text3:#5a6280;--accent:#b91c1c;--accent2:#dc2626;--green:#16a34a;--red:#dc2626;--red-dim:rgba(220,38,38,.14);--radius:6px;--font:'IBM Plex Sans','Helvetica Neue',Arial,sans-serif;--mono:'IBM Plex Mono','Courier New',monospace;}
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
.btn-submit{width:100%;background:var(--accent);color:#fff;font-weight:600;font-size:14px;padding:11px;border-radius:var(--radius);margin-top:8px;border:none;cursor:pointer;font-family:var(--font);transition:background .2s;}
.btn-submit:hover{background:var(--accent2);}
.alert{font-size:12px;text-align:center;padding:10px 12px;border-radius:var(--radius);margin-bottom:16px;}
.alert-err{color:var(--red);background:var(--red-dim);}
.alert-ok{color:var(--green);background:rgba(22,163,74,.12);}
.back{display:block;text-align:center;margin-top:20px;font-size:13px;color:var(--text3);text-decoration:none;}
.back:hover{color:var(--text);}
.hint{font-size:12px;color:var(--text3);text-align:center;margin-bottom:20px;line-height:1.6;}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <h1>{{ strtoupper($settings['company_name'] ?? 'QUICK PRINTS') }}</h1>
    <p>Password Reset</p>
  </div>

  @if(session('status'))
    <div class="alert alert-ok">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-err">{{ $errors->first() }}</div>
  @endif

  <p class="hint">Enter your email address and we'll send you a link to reset your password.</p>

  <form method="POST" action="{{ route('bms.password.email') }}">
    @csrf
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}"
             placeholder="you@company.com" required autofocus>
    </div>
    <button type="submit" class="btn-submit">Send Reset Link →</button>
  </form>

  <a href="{{ route('bms.login') }}" class="back">← Back to Sign In</a>
</div>
</body>
</html>
