<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#b91c1c">
<title>{{ $settings['company_name'] ?? 'QuickPrints' }} — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#111318;--bg2:#1a1d24;--bg3:#222730;--border:#2e3340;--border2:#3a4050;--text:#e8eaf0;--text2:#9ba3b8;--text3:#5a6280;--accent:#b91c1c;--accent2:#dc2626;--red:#dc2626;--red-dim:rgba(220,38,38,0.14);--radius:6px;--font:'IBM Plex Sans','Helvetica Neue',Arial,sans-serif;--mono:'IBM Plex Mono','Courier New',monospace;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--font);background:linear-gradient(135deg,#1e2227 0%,#2d333b 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;color:var(--text);}
a{color:inherit;text-decoration:none;}
button{font-family:var(--font);cursor:pointer;border:none;outline:none;}
input{font-family:var(--font);outline:none;}

/* LOADER */
#loading{position:fixed;inset:0;background:var(--bg);z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;transition:opacity .4s ease;}
#loading.fade-out{opacity:0;pointer-events:none;}
.loader-logo{font-size:28px;font-weight:700;letter-spacing:2px;color:var(--accent);}
.loader-logo span{color:var(--text2);}
.loader-bar{width:200px;height:3px;background:var(--bg3);border-radius:2px;overflow:hidden;}
.loader-bar-fill{height:100%;background:var(--accent);border-radius:2px;animation:loadfill 1.8s ease-in-out infinite;}
@keyframes loadfill{0%{width:0%;margin-left:0}50%{width:70%;margin-left:0}100%{width:0%;margin-left:100%}}
.loader-text{font-size:12px;color:var(--text3);font-family:var(--mono);}

/* LOGIN */
.login-box{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:40px;width:380px;max-width:90vw;box-shadow:0 24px 80px rgba(0,0,0,.35);transition:opacity .4s ease;}
.login-logo{text-align:center;margin-bottom:32px;}
.login-logo h1{font-size:24px;font-weight:700;color:var(--accent);letter-spacing:1px;}
.login-logo p{font-size:12px;color:var(--text3);margin-top:4px;font-family:var(--mono);}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:11px;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:10px 12px;color:var(--text);font-size:14px;transition:border-color .2s;}
.form-group input:focus{border-color:var(--accent);}
.password-wrap{position:relative;}
.password-wrap input{padding-right:42px;}
.password-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:var(--bg2);color:var(--text2);display:flex;align-items:center;justify-content:center;font-size:14px;line-height:1;cursor:pointer;}
.password-toggle:hover{color:var(--text);border-color:var(--border2);}
.btn-login{width:100%;background:var(--accent);color:#fff;font-weight:600;font-size:14px;padding:11px;border-radius:var(--radius);margin-top:8px;transition:background .2s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-login:hover:not(:disabled){background:var(--accent2);}
.btn-login:disabled{opacity:.65;cursor:not-allowed;}
.login-err{color:var(--red);font-size:12px;text-align:center;margin-top:10px;padding:8px;background:var(--red-dim);border-radius:var(--radius);}
.spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none;}
.btn-login.loading .spinner{display:block;}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

{{-- Loader overlay --}}
<div id="loading">
  @php
    $parts = explode(' ', strtoupper($settings['company_name'] ?? 'QUICK PRINTS'), 2);
  @endphp
  <div class="loader-logo">{{ $parts[0] }}<span>{{ isset($parts[1]) ? ' '.$parts[1] : '' }}</span></div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <div class="loader-text" id="loader-text">Initializing...</div>
</div>

{{-- Login form --}}
<div class="login-box" id="login-box" style="opacity:0;">
  <div class="login-logo">
    <h1>{{ strtoupper($settings['company_name'] ?? 'QUICK PRINTS') }}</h1>
    <p>Business Management System</p>
  </div>

  @if($errors->any())
    <div class="login-err">{{ $errors->first() }}</div>
  @endif
  @if(session('error'))
    <div class="login-err">{{ session('error') }}</div>
  @endif
  @if(session('status'))
    <div style="color:var(--text2);font-size:12px;text-align:center;margin-bottom:10px;padding:8px;background:var(--bg3);border-radius:var(--radius);">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('bms.login') }}" id="login-form">
    @csrf
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@quickprints.co.ke" autocomplete="username" required autofocus>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <div class="password-wrap">
        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
        <button type="button" class="password-toggle" onclick="togglePwd()" title="Show password">👁</button>
      </div>
    </div>
    <button type="submit" class="btn-login" id="login-btn">
      <span id="btn-text">Sign In →</span>
      <span class="spinner" id="btn-spinner"></span>
    </button>
  </form>
  <div style="text-align:center;margin-top:18px;">
    <a href="{{ route('bms.password.request') }}"
       style="font-size:12px;color:var(--text3);text-decoration:none;">
      Forgot your password?
    </a>
  </div>
</div>

<script>
(function() {
  var hasErrors = {{ ($errors->any() || session('error')) ? 'true' : 'false' }};
  var loader    = document.getElementById('loading');
  var box       = document.getElementById('login-box');
  var ltxt      = document.getElementById('loader-text');
  var msgs      = ['Initializing BMS…', 'Loading resources…', 'Almost ready…', 'Ready.'];

  function showLogin() {
    loader.classList.add('fade-out');
    box.style.opacity = '1';
    setTimeout(function() { loader.style.display = 'none'; }, 420);
  }

  if (hasErrors) {
    loader.style.display = 'none';
    box.style.opacity = '1';
  } else {
    var i = 0;
    var iv = setInterval(function() {
      if (i < msgs.length) ltxt.textContent = msgs[i++];
    }, 380);
    setTimeout(function() {
      clearInterval(iv);
      showLogin();
    }, 1600);
  }

  document.getElementById('login-form').addEventListener('submit', function() {
    var btn  = document.getElementById('login-btn');
    var txt  = document.getElementById('btn-text');
    var spin = document.getElementById('btn-spinner');
    btn.disabled = true;
    btn.classList.add('loading');
    txt.textContent = 'Signing in…';
    spin.style.display = 'block';
    loader.style.display  = 'flex';
    loader.style.opacity  = '1';
    loader.classList.remove('fade-out');
    ltxt.textContent = 'Signing in…';
  });
})();

function togglePwd() {
  var inp  = document.getElementById('password');
  var btn  = document.querySelector('.password-toggle');
  var show = inp.type === 'password';
  inp.type      = show ? 'text' : 'password';
  btn.textContent = show ? '🙈' : '👁';
}
</script>
</body>
</html>
