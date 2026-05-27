@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">Add Petty Cash Entry</div></div>
  <a href="{{ route('bms.pettycash.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:680px;">
  <form method="POST" action="{{ route('bms.pettycash.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Date <span style="color:var(--red)">*</span></label>
        <input type="date" name="date" value="{{ old('date', $entry->date ?? now()->toDateString()) }}" required>
      </div>
      <div class="fld">
        <label>Branch <span style="color:var(--red)">*</span></label>
        <select name="branch" required>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $entry->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Description <span style="color:var(--red)">*</span></label>
        <input type="text" name="description" value="{{ old('description', $entry->description) }}" required placeholder="What was purchased?">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount (KSh) <span style="color:var(--red)">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $entry->amount) }}" required min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          <option value="pending" {{ old('status', $entry->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ old('status', $entry->status) === 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="rejected" {{ old('status', $entry->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.pettycash.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Add Entry</button>
    </div>
  </form>
</div>
@endsection
