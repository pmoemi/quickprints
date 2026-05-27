@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">My Profile</div>
    <div class="page-subtitle">{{ $user->role ?? 'User' }} · {{ $user->branch === 'all' ? 'All Branches' : ($user->branch ?? '—') }}</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

  {{-- Profile Info Card --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Account Details</div>
    </div>

    @if(session('success') && !str_contains(session('success'), 'Password'))
      <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('bms.profile.update') }}">
      @csrf @method('PUT')

      <div class="form-row">
        {{-- Avatar / Initials --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);">
          <div class="avatar" style="width:56px;height:56px;font-size:20px;font-weight:700;
               background:{{ $staff?->color ? $staff->color.'20' : 'var(--accent-dim)' }};
               color:{{ $staff?->color ?? 'var(--accent)' }};">
            {{ strtoupper(substr($user->name, 0, 2)) }}
          </div>
          <div>
            <div style="font-weight:700;font-size:16px;">{{ $user->name }}</div>
            <div style="font-size:12px;color:var(--text3);">{{ $user->email }}</div>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $user->name) }}" required>
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email', $user->email) }}" required>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      @if($staff)
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                 value="{{ old('phone', $staff->phone) }}" placeholder="+254 700 000 000">
          @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      @endif

      <div class="form-row" style="margin-top:4px;">
        <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="{{ $user->role ?? '—' }}" disabled>
          </div>
          <div>
            <label class="form-label">Branch</label>
            <input type="text" class="form-control" value="{{ $user->branch === 'all' ? 'All Branches' : ($user->branch ?? '—') }}" disabled>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>

  {{-- Change Password Card --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Change Password</div>
    </div>

    @if(session('success') && str_contains(session('success'), 'Password'))
      <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('bms.profile.password') }}">
      @csrf @method('PUT')

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Current Password <span style="color:var(--danger)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="current_password" id="cur-pw"
                   class="form-control @error('current_password') is-invalid @enderror"
                   autocomplete="current-password" required>
            <button type="button" onclick="togglePw('cur-pw',this)"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:14px;">👁</button>
          </div>
          @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">New Password <span style="color:var(--danger)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password" id="new-pw"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password" minlength="8" required>
            <button type="button" onclick="togglePw('new-pw',this)"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:14px;">👁</button>
          </div>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div style="font-size:11px;color:var(--text3);margin-top:4px;">Minimum 8 characters</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Confirm New Password <span style="color:var(--danger)">*</span></label>
          <div style="position:relative;">
            <input type="password" name="password_confirmation" id="conf-pw"
                   class="form-control" autocomplete="new-password" minlength="8" required>
            <button type="button" onclick="togglePw('conf-pw',this)"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:14px;">👁</button>
          </div>
        </div>
      </div>

      <div id="pw-strength" style="margin-bottom:16px;display:none;">
        <div style="font-size:11px;color:var(--text3);margin-bottom:4px;">Password strength</div>
        <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden;">
          <div id="pw-bar" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
        </div>
        <div id="pw-label" style="font-size:11px;margin-top:3px;"></div>
      </div>

      <div style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">Update Password</button>
      </div>
    </form>
  </div>

</div>

<style>
  @media(max-width:768px){
    div[style*="grid-template-columns:1fr 1fr"]{
      grid-template-columns:1fr !important;
    }
  }
  .invalid-feedback{ display:block; color:var(--danger); font-size:12px; margin-top:4px; }
  .is-invalid{ border-color:var(--danger) !important; }
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
  if(!inp) return;
  inp.addEventListener('input', function(){
    var v = this.value;
    if(!v){ wrap.style.display='none'; return; }
    wrap.style.display='block';
    var score = 0;
    if(v.length >= 8) score++;
    if(v.length >= 12) score++;
    if(/[A-Z]/.test(v)) score++;
    if(/[0-9]/.test(v)) score++;
    if(/[^A-Za-z0-9]/.test(v)) score++;
    var pct = (score/5)*100;
    var color = score<=1?'#ef4444':score<=2?'#f97316':score<=3?'#eab308':'#22c55e';
    var text = score<=1?'Weak':score<=2?'Fair':score<=3?'Good':'Strong';
    bar.style.width = pct+'%';
    bar.style.background = color;
    lbl.textContent = text;
    lbl.style.color = color;
  });
})();
</script>
@endsection
