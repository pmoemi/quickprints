@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">My Profile</div>
    <div class="page-subtitle">{{ $user->role ?? 'User' }} · {{ $user->branch === 'all' ? 'All Branches' : ($user->branch ?? '—') }}</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

  {{-- Profile Info --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Account Details</div></div>

    @if(session('success') && !str_contains(session('success'), 'Password'))
      <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if($errors->hasAny(['name','email','phone']))
      <div class="alert alert-danger" style="margin-bottom:16px;">
        <ul style="margin:0;padding-left:1.2rem;">
          @foreach (['name', 'email', 'phone'] as $field)
            @foreach ($errors->get($field) as $message)<li>{{ $message }}</li>@endforeach
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('bms.profile.update') }}">
      @csrf @method('PUT')

      {{-- Avatar row --}}
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        <div class="avatar" style="width:52px;height:52px;font-size:20px;font-weight:700;flex-shrink:0;
             background:{{ $staff?->color ? $staff->color.'20' : 'var(--accent-dim)' }};
             color:{{ $staff?->color ?? 'var(--accent)' }};">
          {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
          <div style="font-weight:700;font-size:15px;line-height:1.2;">{{ $user->name }}</div>
          <div style="font-size:12px;color:var(--text3);">{{ $user->email }}</div>
        </div>
      </div>

      <div class="form-row">
        <div class="fld">
          <label>Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Your full name">
        </div>
      </div>

      <div class="form-row">
        <div class="fld">
          <label>Email Address <span style="color:var(--red)">*</span></label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="your@email.com">
        </div>
      </div>

      @if($staff)
      <div class="form-row">
        <div class="fld">
          <label>Phone</label>
          <input type="tel" name="phone" value="{{ old('phone', $staff->phone) }}" placeholder="+254 700 000 000">
        </div>
      </div>
      @endif

      <div class="form-row cols-2">
        <div class="fld">
          <label>Role</label>
          <input type="text" value="{{ $user->role ?? '—' }}" disabled style="opacity:.6;cursor:not-allowed;">
        </div>
        <div class="fld">
          <label>Branch</label>
          <input type="text" value="{{ $user->branch === 'all' ? 'All Branches' : ($user->branch ?? '—') }}" disabled style="opacity:.6;cursor:not-allowed;">
        </div>
      </div>

      <div style="margin-top:4px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>

  {{-- Change Password --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Change Password</div></div>

    @if(session('success') && str_contains(session('success'), 'Password'))
      <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if($errors->hasAny(['current_password','password']))
      <div class="alert alert-danger" style="margin-bottom:16px;">
        <ul style="margin:0;padding-left:1.2rem;">
          @foreach (['current_password', 'password'] as $field)
            @foreach ($errors->get($field) as $message)<li>{{ $message }}</li>@endforeach
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('bms.profile.password') }}">
      @csrf @method('PUT')

      <div class="form-row">
        <div class="fld">
          <label>Current Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="current_password" id="cur-pw" autocomplete="current-password" required
                   style="padding-right:36px;">
            <button type="button" onclick="togglePw('cur-pw',this)"
                    style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:13px;line-height:1;">👁</button>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="fld">
          <label>New Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password" id="new-pw" autocomplete="new-password" minlength="8" required
                   style="padding-right:36px;">
            <button type="button" onclick="togglePw('new-pw',this)"
                    style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:13px;line-height:1;">👁</button>
          </div>
          <div style="font-size:11px;color:var(--text3);margin-top:4px;">Minimum 8 characters</div>
        </div>
      </div>

      {{-- Strength meter --}}
      <div id="pw-strength" style="margin-bottom:14px;display:none;">
        <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden;margin-bottom:3px;">
          <div id="pw-bar" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
        </div>
        <div id="pw-label" style="font-size:11px;"></div>
      </div>

      <div class="form-row">
        <div class="fld">
          <label>Confirm New Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password_confirmation" id="conf-pw" autocomplete="new-password" minlength="8" required
                   style="padding-right:36px;">
            <button type="button" onclick="togglePw('conf-pw',this)"
                    style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:13px;line-height:1;">👁</button>
          </div>
        </div>
      </div>

      <div style="margin-top:4px;">
        <button type="submit" class="btn btn-primary">Update Password</button>
      </div>
    </form>
  </div>

</div>

<style>
  @media(max-width:768px){
    div[style*="grid-template-columns:1fr 1fr;gap:24px"]{
      grid-template-columns:1fr !important;
    }
  }
</style>

<script>
function togglePw(id, btn) {
  var inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}

(function(){
  var inp = document.getElementById('new-pw');
  var bar = document.getElementById('pw-bar');
  var lbl = document.getElementById('pw-label');
  var wrap = document.getElementById('pw-strength');
  if (!inp) return;
  inp.addEventListener('input', function(){
    var v = this.value;
    if (!v) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    var score = 0;
    if (v.length >= 8)  score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    var colors = ['#ef4444','#ef4444','#f97316','#eab308','#22c55e'];
    var labels = ['Too short','Weak','Fair','Good','Strong'];
    bar.style.width = (score / 5 * 100) + '%';
    bar.style.background = colors[score] || '#22c55e';
    lbl.textContent = labels[score] || 'Strong';
    lbl.style.color = colors[score] || '#22c55e';
  });
})();
</script>
@endsection
