@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Attendance</div>
    <div class="page-subtitle">{{ $records->count() }} record(s) for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
  </div>
</div>

<div class="grid-2" style="gap:20px;align-items:flex-start;">
  <div>
    <div class="filter-bar">
      <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <div class="fld" style="margin-bottom:0;">
          <input type="date" name="date" value="{{ $date }}" class="search-input" onchange="this.form.submit()">
        </div>
      </form>
    </div>

    <div class="card">
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>Staff</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            @forelse($records as $rec)
              <tr>
                <td style="font-weight:600;">{{ $rec->staff_name }}</td>
                <td class="mono" style="font-size:12px;">{{ $rec->check_in ?? '—' }}</td>
                <td class="mono" style="font-size:12px;">{{ $rec->check_out ?? '—' }}</td>
                <td>
                  @php $statusColors = ['present'=>'badge-green','absent'=>'badge-red','late'=>'badge-yellow','half_day'=>'badge-orange']; @endphp
                  <span class="badge {{ $statusColors[$rec->status ?? 'present'] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$rec->status ?? 'present')) }}</span>
                </td>
                <td>
                  <form method="POST" action="{{ route('bms.attendance.destroy', $rec->id) }}" onsubmit="return confirm('Delete record?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">⏱️</div><p>No attendance recorded for this date</p></div></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Mark Attendance</div></div>
    <form method="POST" action="{{ route('bms.attendance.store') }}">
      @csrf
      <div class="fld" style="margin-bottom:12px;">
        <label>Staff Member</label>
        <select name="staff_id" required>
          <option value="">— Select —</option>
          @foreach($staff as $s)
            <option value="{{ $s->id }}">{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld" style="margin-bottom:12px;">
        <label>Date</label>
        <input type="date" name="date" value="{{ $date }}" required>
      </div>
      <div class="form-row cols-2" style="margin-bottom:12px;">
        <div class="fld">
          <label>Check In</label>
          <input type="time" name="check_in">
        </div>
        <div class="fld">
          <label>Check Out</label>
          <input type="time" name="check_out">
        </div>
      </div>
      <div class="fld" style="margin-bottom:12px;">
        <label>Status</label>
        <select name="status">
          <option value="present">Present</option>
          <option value="absent">Absent</option>
          <option value="late">Late</option>
          <option value="half_day">Half Day</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Mark Attendance</button>
    </form>
  </div>
</div>
@endsection
