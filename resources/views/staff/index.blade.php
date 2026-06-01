@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Staff</div>
    <div class="page-subtitle">
      {{ $staff->count() }} staff record(s)
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
    Staff records filtered to <strong>{{ $branch }}</strong> (plus company-wide).
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
        <tr id="row-{{ $member->id }}">
          <td style="padding:10px 14px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="avatar" style="background:{{ $member->color ? $member->color.'20' : 'var(--accent-dim)' }};color:{{ $member->color ?? 'var(--accent)' }};">
                {{ strtoupper(substr($member->name, 0, 2)) }}
              </div>
              <div>
                <div style="font-weight:600;font-size:13px;">{{ $member->name }}</div>
                <div style="font-size:11px;color:var(--text3);font-family:var(--mono);">{{ $member->phone ?? '' }}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="badge badge-blue">{{ $member->role }}</span>
            @if(!empty($member->is_designer))
              <span class="badge" style="margin-left:4px;background:rgba(234,179,8,.15);color:#ca8a04;border:1px solid rgba(234,179,8,.3);">Designer</span>
            @endif
          </td>
          <td class="col-email" style="font-size:12px;color:var(--text2);">{{ $member->email ?? '—' }}</td>
          <td class="col-branch"><span style="font-size:12px;color:var(--text2);">{{ ($member->branch === 'all' || !$member->branch) ? 'All' : $member->branch }}</span></td>
          <td class="col-salary mono" style="font-size:12px;">{{ $bmsCurrency }} {{ number_format($member->salary ?? 0) }}</td>
          <td>
            <span class="badge {{ $member->active ? 'badge-green' : '' }}"
                  style="{{ !$member->active ? 'background:rgba(107,114,128,.15);color:#6b7280;border:1px solid rgba(107,114,128,.3);' : '' }}">
              {{ $member->active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <a href="{{ route('bms.staff.edit', $member->id) }}" class="btn btn-secondary btn-sm">Edit</a>
              @if(\App\Support\BmsPermissions::canResetStaffPasswords(auth()->user()?->role) && $member->user_id)
                <button type="button" class="btn btn-secondary btn-sm"
                        onclick="togglePwReset({{ $member->id }})"
                        id="btn-pw-{{ $member->id }}"
                        title="Reset password for {{ $member->name }}">🔑</button>
              @endif
              @if(auth()->user()?->role === 'Admin' && $member->user_id && $member->role !== 'Admin')
                <form method="POST" action="{{ route('bms.staff.impersonate', $member->user_id) }}"
                      onsubmit="return confirm('View the system as {{ addslashes($member->name) }}?')">
                  @csrf
                  <button type="submit" class="btn btn-secondary btn-sm" title="View as {{ $member->name }}">👤</button>
                </form>
              @endif
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="openDelModal({{ $member->id }}, '{{ addslashes($member->name) }}')">Delete</button>
            </div>
          </td>
        </tr>

        {{-- Inline password reset row (hidden by default) --}}
        @if(\App\Support\BmsPermissions::canResetStaffPasswords(auth()->user()?->role) && $member->user_id)
          <tr id="pw-row-{{ $member->id }}" style="display:none;background:var(--bg3);">
            <td colspan="7" style="padding:12px 16px;">
              <form method="POST" action="{{ route('bms.staff.reset-password', $member->id) }}"
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
                    <input type="password" name="new_password" id="pw-inp-{{ $member->id }}"
                           class="fld" style="width:100%;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:7px 32px 7px 10px;color:var(--text);font-size:13px;"
                           minlength="8" placeholder="Min 8 chars" autocomplete="new-password">
                    <button type="button" tabindex="-1"
                            onclick="togglePwVis('pw-inp-{{ $member->id }}',this)"
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
                          onclick="togglePwReset({{ $member->id }})">Cancel</button>
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
  </div>
</div>

{{-- Delete + Transfer Modal --}}
<div id="del-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--bg2);border:1px solid var(--border);border-radius:8px;padding:24px;width:100%;max-width:440px;margin:16px;">
    <div style="font-size:15px;font-weight:700;margin-bottom:8px;">Delete Staff Member</div>
    <p style="font-size:13px;color:var(--text2);margin:0 0 16px;">
      You are deleting <strong id="dm-name"></strong>.
      Select a staff member to receive their records before deletion.
    </p>
    <form method="POST" id="dm-form">
      @csrf
      @method('DELETE')
      <div style="margin-bottom:20px;">
        <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);display:block;margin-bottom:6px;">Transfer records to</label>
        <select name="transfer_to_id" id="dm-select"
                style="width:100%;padding:8px 10px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius,4px);color:var(--text);font-size:13px;">
          <option value="">— No transfer (clear references) —</option>
        </select>
        <div style="font-size:11px;color:var(--text3);margin-top:5px;">
          Sales logs, print jobs, attendance, leave, and payroll records will be reassigned to the selected staff.
        </div>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeDelModal()">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete Staff</button>
      </div>
    </form>
  </div>
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
const _staffList = @json($staff->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values());
const _staffIndexUrl = '{{ route("bms.staff.index") }}';

function openDelModal(id, name) {
  const modal = document.getElementById('del-modal');
  document.getElementById('dm-name').textContent = name;
  document.getElementById('dm-form').action = _staffIndexUrl + '/' + id;
  const sel = document.getElementById('dm-select');
  sel.innerHTML = '<option value="">— No transfer (clear references) —</option>';
  _staffList.filter(s => s.id != id).forEach(s => {
    const o = document.createElement('option');
    o.value = s.id;
    o.textContent = s.name;
    sel.appendChild(o);
  });
  modal.style.display = 'flex';
}

function closeDelModal() {
  document.getElementById('del-modal').style.display = 'none';
}

document.getElementById('del-modal').addEventListener('click', function(e) {
  if (e.target === this) closeDelModal();
});

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
