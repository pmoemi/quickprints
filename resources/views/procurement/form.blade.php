@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">Add Procurement Entry</div></div>
  <a href="{{ route('bms.procurement.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:680px;">
  <form method="POST" action="{{ route('bms.procurement.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Date <span style="color:var(--red)">*</span></label>
        <input type="date" name="date" value="{{ old('date', $entry->date ?? now()->toDateString()) }}" required>
      </div>
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="">— Select —</option>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $entry->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Supplier</label>
        <input type="text" name="supplier" value="{{ old('supplier', $entry->supplier) }}" placeholder="Supplier name">
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          <option value="pending" {{ old('status', $entry->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="ordered" {{ old('status', $entry->status) === 'ordered' ? 'selected' : '' }}>Ordered</option>
          <option value="received" {{ old('status', $entry->status) === 'received' ? 'selected' : '' }}>Received</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Description / Item <span style="color:var(--red)">*</span></label>
        <input type="text" name="description" value="{{ old('description', $entry->description) }}" required placeholder="e.g. Eco Solvent Ink - 4 litres">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount (KSh) <span style="color:var(--red)">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $entry->amount) }}" required min="0" step="0.01" placeholder="0">
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.procurement.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Add Entry</button>
    </div>
  </form>
</div>
@endsection
