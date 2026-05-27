@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">New Leave Request</div></div>
  <a href="{{ route('bms.leave.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:680px;">
  <form method="POST" action="{{ route('bms.leave.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Staff Member <span style="color:var(--red)">*</span></label>
        <select name="staff_id" required>
          <option value="">— Select —</option>
          @foreach($staff as $s)
            <option value="{{ $s->id }}">{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Leave Type</label>
        <select name="leave_type">
          @foreach(['Annual','Sick','Maternity','Paternity','Compassionate','Unpaid'] as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Start Date <span style="color:var(--red)">*</span></label>
        <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
      </div>
      <div class="fld">
        <label>End Date</label>
        <input type="date" name="end_date" value="{{ old('end_date') }}">
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Reason</label>
        <textarea name="reason" rows="3" placeholder="Brief reason for leave…">{{ old('reason') }}</textarea>
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.leave.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Submit Request</button>
    </div>
  </form>
</div>
@endsection
