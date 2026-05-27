@extends('layouts.bms')

@section('content')
@php $editing = $member->exists; $isAdminUser = $isAdminUser ?? false; @endphp

<div class="page-header">
  <div><div class="page-title">{{ $editing ? 'Edit Staff Member' : 'Add Staff Member' }}</div></div>
  <a href="{{ route('bms.staff.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:720px;">
  <form method="POST" action="{{ $editing ? route('bms.staff.update', $member->id) : route('bms.staff.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Full Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="{{ old('name', $member->name) }}" required placeholder="Staff full name">
      </div>
      <div class="fld">
        <label>Role <span style="color:var(--red)">*</span></label>
        @if($isAdminUser && auth()->user()?->role !== 'Admin')
          {{-- Non-admin cannot change another Admin's role --}}
          <input type="text" value="Admin (built-in)" disabled style="opacity:.6;cursor:not-allowed;">
          <input type="hidden" name="role" value="Admin">
          <div style="font-size:11px;color:var(--yellow);margin-top:4px;">⚠ Only an Admin can change this role.</div>
        @else
          <select name="role" required>
            <option value="">— Select —</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ old('role', $member->role) === $r ? 'selected' : '' }}>
                {{ $r }}{{ $r === 'Admin' ? ' (built-in superuser)' : '' }}
              </option>
            @endforeach
          </select>
          @if($isAdminUser)
            <div style="font-size:11px;color:var(--yellow);margin-top:4px;">⚠ This user has full Admin access. Changing their role will remove all Admin privileges.</div>
          @endif
        @endif
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $member->email) }}" placeholder="staff@quickprints.co.ke">
      </div>
      <div class="fld">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" placeholder="07xx xxx xxx">
      </div>
    </div>
    <div class="form-row cols-3">
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="all" {{ old('branch', $member->branch) === 'all' ? 'selected' : '' }}>All Branches</option>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $member->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Salary ({{ $bmsCurrency }})</label>
        <input type="number" name="salary" value="{{ old('salary', $member->salary) }}" min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="active">
          <option value="1" {{ old('active', $member->active ?? true) ? 'selected' : '' }}>Active</option>
          <option value="0" {{ !old('active', $member->active ?? true) ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
          <input type="checkbox" name="is_designer" value="1" {{ old('is_designer', $member->is_designer ?? false) ? 'checked' : '' }} style="width:16px;height:16px;">
          Is Designer
        </label>
        <div style="font-size:11px;color:var(--text3);margin-top:4px;">
          Designers appear in job assignment lists and receive jobs on the designer board.
        </div>
      </div>
    </div>

    @if(!$editing)
    <div class="form-row cols-2">
      <div class="fld">
        <label>Login Password</label>
        <div style="position:relative;">
          <input type="password" name="password" id="nst-pass" placeholder="Leave blank for no login access">
          <button type="button" onclick="toggleStaffPwd()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:var(--bg2);border:1px solid var(--border);border-radius:4px;cursor:pointer;padding:2px 6px;color:var(--text2);">👁</button>
        </div>
      </div>
    </div>
    @endif

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.staff.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Staff' : 'Add Staff Member' }}</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function toggleStaffPwd() {
  const inp = document.getElementById('nst-pass');
  inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
