@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">Add Expense</div></div>
  <a href="{{ route('bms.opex.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:700px;">
  <form method="POST" action="{{ route('bms.opex.store') }}">
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
        <input type="text" name="description" value="{{ old('description', $entry->description) }}" required placeholder="e.g. Electricity bill">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount ({{ $bmsCurrency }}) <span style="color:var(--red)">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $entry->amount) }}" required min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Payment Method</label>
        <select name="pay_method">
          @foreach($bmsPaymentMethods as $m)
            <option value="{{ $m }}" {{ old('pay_method', $entry->pay_method) === $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.opex.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Record Expense</button>
    </div>
  </form>
</div>
@endsection
