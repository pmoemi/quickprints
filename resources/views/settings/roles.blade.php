@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

@if(session('success'))
  <div style="background:rgba(34,197,94,.1);border:1px solid var(--green);border-radius:6px;padding:10px 14px;margin-bottom:16px;color:var(--green);font-size:13px;">
    {{ session('success') }}
  </div>
@endif

@if($errors->has('role'))
  <div style="background:rgba(239,68,68,.1);border:1px solid var(--red);border-radius:6px;padding:10px 14px;margin-bottom:16px;color:var(--red);font-size:13px;">
    {{ $errors->first('role') }}
  </div>
@endif

<div style="display:grid;grid-template-columns:220px 1fr;gap:16px;align-items:start;">

  {{-- ── Left panel: role list ─────────────────────────────── --}}
  <div class="card" style="padding:0;">
    <div style="padding:12px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
      <span style="font-size:13px;font-weight:600;color:var(--text2);">Roles</span>
      <a href="{{ route('bms.settings.roles') }}?add=1" class="btn btn-primary btn-sm">+ Add</a>
    </div>

    {{-- Admin (built-in) --}}
    <div style="padding:10px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
      <span style="font-size:13px;font-weight:600;color:var(--text);">Admin</span>
      <span style="font-size:10px;color:var(--text3);">Built-in</span>
    </div>

    @foreach($roles as $roleName => $roleData)
      @php $active = ($editRole === $roleName); @endphp
      <a href="{{ route('bms.settings.roles') }}?edit={{ urlencode($roleName) }}"
         style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;text-decoration:none;border-bottom:1px solid var(--border);
                {{ $active ? 'background:var(--accent);' : 'background:transparent;' }}">
        <span style="font-size:13px;font-weight:600;{{ $active ? 'color:#fff;' : 'color:var(--text);' }}">{{ $roleName }}</span>
        <span style="font-size:10px;{{ $active ? 'color:rgba(255,255,255,.65);' : 'color:var(--text3);' }}">
          {{ count(array_filter($roleData['perms'] ?? [], fn($p) => !empty($p))) }} modules
        </span>
      </a>
    @endforeach

    <div style="padding:10px 14px;">
      <a href="{{ route('bms.settings.roles') }}?add=1"
         style="font-size:12px;color:var(--accent);text-decoration:none;{{ $addNew ? 'font-weight:600;' : '' }}">
        + New Role
      </a>
    </div>
  </div>

  {{-- ── Right panel: matrix editor ───────────────────────── --}}
  <div>

    @if($editRole && isset($roles[$editRole]))
      @php $rd = $roles[$editRole]; @endphp

      <div class="card">
        {{-- Header row --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
          <div>
            <div class="card-title">{{ $editRole }}</div>
            <div style="font-size:12px;color:var(--text3);margin-top:2px;">Configure module access for this role</div>
          </div>
          <form method="POST" action="{{ route('bms.settings.roles.destroy', urlencode($editRole)) }}"
                onsubmit="return confirm('Delete role \'{{ $editRole }}\'? This cannot be undone.');" style="flex-shrink:0;">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete Role</button>
          </form>
        </div>

        <form method="POST" action="{{ route('bms.settings.roles.update', urlencode($editRole)) }}" id="editForm">
          @csrf @method('PUT')

          {{-- Role name --}}
          <div class="fld" style="max-width:300px;margin-bottom:20px;">
            <label>Role Name</label>
            <input type="text" name="new_name" value="{{ old('new_name', $editRole) }}" required maxlength="60">
            @error('new_name')<span style="color:var(--red);font-size:12px;">{{ $message }}</span>@enderror
          </div>

          {{-- Permission matrix --}}
          <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
            <thead>
              <tr style="border-bottom:2px solid var(--border);">
                <th style="text-align:left;padding:10px 0;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;">
                  Module
                </th>
                @foreach(['read' => 'Read', 'write' => 'Write', 'create' => 'Create'] as $perm => $label)
                  <th style="text-align:center;width:90px;padding:10px 8px;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;">
                    {{ $label }}
                  </th>
                @endforeach
                <th style="text-align:right;padding:10px 0 10px 8px;white-space:nowrap;">
                  <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--text3);cursor:pointer;">
                    <input type="checkbox" id="sa_edit" onchange="document.querySelectorAll('#editForm input[type=checkbox]:not(#sa_edit):not([name=allBranches])').forEach(c=>c.checked=this.checked)">
                    Select All
                  </label>
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($groups as $groupKey => $group)
                @php $gp = $rd['perms'][$groupKey] ?? []; @endphp
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:14px 0;font-size:13px;font-weight:600;color:var(--text);">{{ $group['label'] }}</td>
                  @foreach(['read', 'write', 'create'] as $perm)
                    <td style="text-align:center;padding:14px 8px;">
                      <input type="checkbox" name="perms[{{ $groupKey }}][]" value="{{ $perm }}"
                             {{ in_array($perm, $gp) ? 'checked' : '' }}>
                    </td>
                  @endforeach
                  <td></td>
                </tr>
              @endforeach
            </tbody>
          </table>

          {{-- Special capabilities --}}
          <div style="padding:12px 0;border-top:1px solid var(--border);margin-bottom:8px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:10px;">Special Capabilities</div>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;margin-bottom:10px;display:flex;">
              <input type="checkbox" name="allBranches" value="1" {{ ($rd['allBranches'] ?? false) ? 'checked' : '' }}>
              <span>
                <strong>All Branches Access</strong>
                <span style="color:var(--text3);"> — can view and switch between all branches</span>
              </span>
            </label>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;display:flex;">
              <input type="checkbox" name="resetStaffPasswords" value="1" {{ ($rd['resetStaffPasswords'] ?? false) ? 'checked' : '' }}>
              <span>
                <strong>Reset Staff Passwords</strong>
                <span style="color:var(--text3);"> — can reset login passwords for other staff members</span>
              </span>
            </label>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;display:flex;">
              <input type="checkbox" name="viewDashboardSummaries" value="1" {{ ($rd['viewDashboardSummaries'] ?? false) ? 'checked' : '' }}>
              <span>
                <strong>Dashboard Financial Summaries</strong>
                <span style="color:var(--text3);"> — can see sales, revenue, branch rankings, and payment totals on the dashboard</span>
              </span>
            </label>
          </div>

          <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('bms.settings.roles') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>

    @elseif($addNew)

      <div class="card">
        <div style="margin-bottom:20px;">
          <div class="card-title">New Role</div>
          <div style="font-size:12px;color:var(--text3);margin-top:2px;">Define module access for the new role</div>
        </div>

        <form method="POST" action="{{ route('bms.settings.roles.store') }}" id="addForm">
          @csrf

          <div class="fld" style="max-width:300px;margin-bottom:20px;">
            <label>Role Name <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Procurement Officer" required maxlength="60" autofocus>
            @error('name')<span style="color:var(--red);font-size:12px;">{{ $message }}</span>@enderror
          </div>

          <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
            <thead>
              <tr style="border-bottom:2px solid var(--border);">
                <th style="text-align:left;padding:10px 0;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;">Module</th>
                @foreach(['Read', 'Write', 'Create'] as $col)
                  <th style="text-align:center;width:90px;padding:10px 8px;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;">{{ $col }}</th>
                @endforeach
                <th style="text-align:right;padding:10px 0 10px 8px;white-space:nowrap;">
                  <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--text3);cursor:pointer;">
                    <input type="checkbox" id="sa_add" onchange="document.querySelectorAll('#addForm input[type=checkbox]:not(#sa_add):not([name=allBranches])').forEach(c=>c.checked=this.checked)">
                    Select All
                  </label>
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($groups as $groupKey => $group)
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:14px 0;font-size:13px;font-weight:600;color:var(--text);">{{ $group['label'] }}</td>
                  @foreach(['read', 'write', 'create'] as $perm)
                    <td style="text-align:center;padding:14px 8px;">
                      <input type="checkbox" name="perms[{{ $groupKey }}][]" value="{{ $perm }}"
                             {{ in_array($perm, old("perms.{$groupKey}", [])) ? 'checked' : '' }}>
                    </td>
                  @endforeach
                  <td></td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <div style="padding:12px 0;border-top:1px solid var(--border);margin-bottom:8px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:10px;">Special Capabilities</div>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;margin-bottom:10px;">
              <input type="checkbox" name="allBranches" value="1">
              <span>
                <strong>All Branches Access</strong>
                <span style="color:var(--text3);"> — can view and switch between all branches</span>
              </span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;margin-bottom:10px;">
              <input type="checkbox" name="resetStaffPasswords" value="1">
              <span>
                <strong>Reset Staff Passwords</strong>
                <span style="color:var(--text3);"> — can reset login passwords for other staff members</span>
              </span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
              <input type="checkbox" name="viewDashboardSummaries" value="1">
              <span>
                <strong>Dashboard Financial Summaries</strong>
                <span style="color:var(--text3);"> — can see sales, revenue, branch rankings, and payment totals on the dashboard</span>
              </span>
            </label>
          </div>

          <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary">Create Role</button>
            <a href="{{ route('bms.settings.roles') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>

    @else

      <div class="card" style="text-align:center;padding:48px 24px;">
        <div style="font-size:32px;margin-bottom:12px;">🔑</div>
        <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:6px;">Select a role to configure</div>
        <div style="font-size:13px;color:var(--text3);margin-bottom:20px;">Choose a role from the left to edit its permissions, or create a new one.</div>
        <a href="{{ route('bms.settings.roles') }}?add=1" class="btn btn-primary">+ Create New Role</a>
      </div>

    @endif

  </div>
</div>
@endsection
