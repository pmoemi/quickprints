@extends('layouts.bms')

@section('content')
@php
  $unlinkedCount = $staff->filter(fn ($m) => ($m->_user_only ?? $m->_admin_only ?? false))->count();
  $staffCount = $staff->count() - $unlinkedCount;
@endphp
<div class="page-header">
  <div>
    <div class="page-title">Staff</div>
    <div class="page-subtitle">
      {{ $staffCount }} staff record(s)
      @if($unlinkedCount > 0)
        · {{ $unlinkedCount }} unlinked login account(s)
      @endif
      @if($branch !== 'all') · {{ $branch }} branch @endif
    </div>
  </div>
  <a href="{{ route('bms.staff.create') }}" class="btn btn-primary">+ Add Staff</a>
</div>

<form method="GET" action="{{ route('bms.staff.index') }}" class="filter-bar">
  <input class="search-input" name="q" value="{{ $q }}" placeholder="Search name, email, role…" autocomplete="off">
  <select class="filter-select" name="branch" onchange="this.form.submit()">
    <option value="all" @selected($branch === 'all')>All Branches</option>
    @foreach($branches as $br)
      @if($br !== 'all')
        <option value="{{ $br }}" @selected($branch === $br)>{{ $br }}</option>
      @endif
    @endforeach
  </select>
  @if($q || $branch !== 'all')
    <a href="{{ route('bms.staff.index') }}" class="btn btn-secondary btn-sm">Clear</a>
  @endif
</form>

@if($branch !== 'all')
  <div class="alert alert-warn" style="margin-bottom:16px;">
    Staff records filtered to <strong>{{ $branch }}</strong> (plus company-wide). Unlinked login accounts are always shown.
    <a href="{{ route('bms.staff.index', request()->only('q')) }}">View all branches</a>
  </div>
@endif

<div class="card" style="padding:0;overflow:hidden;">
  <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
  <table id="staff-table" style="width:100%;border-collapse:collapse;min-width:560px;">
    <thead>
      <tr>
        <th style="padding:10px 14px;">Name</th>
        <th>Role</th>
        <th class="col-email">Email</th>
        <th class="col-branch">Branch</th>
        <th class="col-salary">Salary</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($staff as $member)
        @php $isUserOnly = $member->_user_only ?? $member->_admin_only ?? false; @endphp
        {{-- Main row --}}
        <tr id="row-{{ $member->id ?? 'usr-'.$member->user_id }}">
          <td style="padding:10px 14px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="avatar" style="background:{{ $member->color ? $member->color.'20' : 'var(--accent-dim)' }};color:{{ $member->color ?? 'var(--accent)' }};">
                {{ strtoupper(substr($member->name, 0, 2)) }}
              </div>
              <div>
                <div style="font-weight:600;font-size:13px;">{{ $member->name }}</div>
                @if($isUserOnly)
                  <div style="font-size:10px;color:var(--text3);">Login account · no staff record</div>
                @else
                  <div style="font-size:11px;color:var(--text3);font-family:var(--mono);">{{ $member->phone ?? '' }}</div>
                @endif
              </div>
            </div>
          </td>
          <td>
            @if($isUserOnly)
              <span class="badge" style="{{ $member->role === 'Admin' ? 'background:rgba(185,28,28,.15);color:#dc2626;border:1px solid rgba(185,28,28,.3);' : 'background:rgba(234,179,8,.15);color:#ca8a04;border:1px solid rgba(234,179,8,.3);' }}">{{ $member->role }}</span>
            @else
              <span class="badge badge-blue">{{ $member->role }}</span>
              @if(!empty($member->is_designer))
                <span class="badge" style="margin-left:4px;background:rgba(234,179,8,.15);color:#ca8a04;border:1px solid rgba(234,179,8,.3);">Designer</span>
              @endif
            @endif
          </td>
          <td class="col-email" style="font-size:12px;color:var(--text2);">{{ $member->email ?? '—' }}</td>
          <td class="col-branch"><span style="font-size:12px;color:var(--text2);">{{ ($member->branch === 'all' || !$member->branch) ? 'All' : $member->branch }}</span></td>
          <td class="col-salary mono" style="font-size:12px;">
            @if(!$isUserOnly){{ $bmsCurrency }} {{ number_format($member->salary ?? 0) }}@else <span style="color:var(--text3);">—</span>@endif
          </td>
          <td><span class="badge badge-green">Active</span></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              @if($isUserOnly)
                <a href="{{ route('bms.staff.create', array_filter([
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->role,
                    'branch' => ($member->branch && $member->branch !== 'all') ? $member->branch : null,
                ])) }}" class="btn btn-primary btn-sm">Link staff</a>
              @else
                <a href="{{ route('bms.staff.edit', $member->id) }}" class="btn btn-secondary btn-sm">Edit</a>
              @endif
              @if(\App\Support\BmsPermissions::canResetStaffPasswords(auth()->user()?->role) && $member->user_id)
                <button type="button" class="btn btn-secondary btn-sm"
                        onclick="togglePwReset('{{ $member->id ?? 'usr-'.$member->user_id }}')"
                        id="btn-pw-{{ $member->id ?? 'usr-'.$member->user_id }}"
                        title="Reset password for {{ $member->name }}">🔑</button>
              @endif
              @if(!$isUserOnly)
                <form method="POST" action="{{ route('bms.staff.destroy', $member->id) }}"
                      onsubmit="return confirm('Delete {{ addslashes($member->name) }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              @endif
            </div>
          </td>
        </tr>

        {{-- Inline password reset row (hidden by default) --}}
        @if(\App\Support\BmsPermissions::canResetStaffPasswords(auth()->user()?->role) && $member->user_id)
          @php $pwRowKey = $member->id ?? 'usr-'.$member->user_id; @endphp
          <tr id="pw-row-{{ $pwRowKey }}" style="display:none;background:var(--bg3);">
            <td colspan="7" style="padding:12px 16px;">
              {{-- Unlinked user rows use a direct user password reset route --}}
              <form method="POST"
                    action="{{ $isUserOnly
                        ? route('bms.staff.reset-password-user', $member->user_id)
                        : route('bms.staff.reset-password', $member->id) }}"
                    style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;">
                @csrf
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                  <div class="avatar" style="width:28px;height:28px;font-size:11px;background:{{ $member->color ? $member->color.'20' : 'var(--accent-dim)' }};color:{{ $member->color ?? 'var(--accent)' }};">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                  </div>
                  <span style="font-size:13px;font-weight:600;">{{ $member->name }}</span>
                  <span style="font-size:11px;color:var(--text3);">· Reset Password</span>
                </div>
                <div style="flex:1;min-width:160px;max-width:220px;">
                  <label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">New Password</label>
                  <div style="position:relative;">
                    <input type="password" name="new_password" id="pw-inp-{{ $pwRowKey }}"
                           class="fld" style="width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:7px 32px 7px 10px;color:var(--text);font-size:13px;"
                           minlength="8" placeholder="Min 8 chars" autocomplete="new-password">
                    <button type="button" tabindex="-1"
                            onclick="togglePwVis('pw-inp-{{ $pwRowKey }}',this)"
                            style="position:absolute;right:6px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:12px;">👁</button>
                  </div>
                </div>
                <div style="flex:1;min-width:160px;max-width:220px;">
                  <label style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Confirm Password</label>
                  <input type="password" name="new_password_confirmation"
                         style="width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:7px 10px;color:var(--text);font-size:13px;"
                         minlength="8" placeholder="Repeat password" autocomplete="new-password">
                </div>
                <div style="display:flex;gap:6px;align-items:flex-end;padding-bottom:1px;">
                  <button type="submit" class="btn btn-primary btn-sm">Save Password</button>
                  <button type="button" class="btn btn-secondary btn-sm"
                          onclick="togglePwReset('{{ $pwRowKey }}')">Cancel</button>
                </div>
              </form>
            </td>
          </tr>
        @endif
      @empty
        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">👤</div><p>No staff members</p></div></td></tr>
      @endforelse
    </tbody>
  </table>
  </div>{{-- /scroll wrapper --}}
</div>

<style>
@media(max-width:640px){
  .col-email,.col-branch,.col-salary{ display:none; }
  .btn-sm{ padding:5px 8px; font-size:11px; }
}
@media(max-width:768px){
  .col-salary{ display:none; }
}
</style>

<script>
function togglePwReset(key) {
  const row = document.getElementById('pw-row-' + key);
  const btn = document.getElementById('btn-pw-' + key);
  if (!row) return;
  const open = row.style.display === 'none' || row.style.display === '';
  row.style.display = open ? 'table-row' : 'none';
  if (btn) { btn.style.background = open ? 'var(--accent-dim)' : ''; btn.style.borderColor = open ? 'var(--accent)' : ''; }
  if (open) { const inp = document.getElementById('pw-inp-' + key); if (inp) inp.focus(); }
}
function togglePwVis(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}
</script>
@endsection
