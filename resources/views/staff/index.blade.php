@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Staff</div>
    <div class="page-subtitle">{{ $staff->count() }} member(s)</div>
  </div>
  <a href="{{ route('bms.staff.create') }}" class="btn btn-primary">+ Add Staff</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Branch</th><th>Salary</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($staff as $member)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div class="avatar" style="background:{{ $member->color ? $member->color.'20' : 'var(--accent-dim)' }};color:{{ $member->color ?? 'var(--accent)' }};">
                  {{ strtoupper(substr($member->name, 0, 2)) }}
                </div>
                <span style="font-weight:600;">{{ $member->name }}</span>
              </div>
            </td>
            <td><span class="badge badge-blue">{{ $member->role }}</span></td>
            <td style="font-size:12px;color:var(--text2);">{{ $member->email ?? '—' }}</td>
            <td class="mono" style="font-size:12px;">{{ $member->phone ?? '—' }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $member->branch === 'all' ? 'All' : ($member->branch ?? '—') }}</span></td>
            <td class="mono">KSh {{ number_format($member->salary ?? 0) }}</td>
            <td>
              @if($member->active)
                <span class="badge badge-green">Active</span>
              @else
                <span class="badge badge-red">Inactive</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.staff.edit', $member->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.staff.destroy', $member->id) }}" onsubmit="return confirm('Delete staff member?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👤</div><p>No staff members</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
