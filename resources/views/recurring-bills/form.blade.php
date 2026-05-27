@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ isset($item->id) ? 'Edit Recurring Bill' : 'New Recurring Bill' }}</div>
    <div class="page-subtitle">{{ isset($item->id) ? 'Update bill details' : 'Add a monthly recurring expense' }}</div>
  </div>
  <a href="{{ route('bms.recurring-bills.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:580px;">
  <div class="card-header"><div class="card-title">Bill Details</div></div>
  <form method="POST" action="{{ isset($item->id) ? route('bms.recurring-bills.update', $item->id) : route('bms.recurring-bills.store') }}">
    @csrf
    @if(isset($item->id)) @method('PATCH') @endif
    <div class="form-row">
      <div class="fld">
        <label>Bill Name</label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" required placeholder="e.g. Office Rent">
        @error('name')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Category</label>
        <input type="text" name="category" value="{{ old('category', $item->category) }}" placeholder="Rent, Utilities...">
      </div>
      <div class="fld">
        <label>Vendor</label>
        <input type="text" name="vendor" value="{{ old('vendor', $item->vendor) }}" placeholder="Vendor name">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount (KES)</label>
        <input type="number" name="amount" value="{{ old('amount', $item->amount) }}" min="0" step="0.01" required>
        @error('amount')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>Due Day of Month</label>
        <input type="number" name="due_day" value="{{ old('due_day', $item->due_day ?? 1) }}" min="1" max="31" required>
        @error('due_day')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="">All Branches</option>
          @foreach($branches as $b)
            <option value="{{ $b }}" {{ old('branch', $item->branch) === $b ? 'selected' : '' }}>{{ $b }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-primary">{{ isset($item->id) ? 'Update' : 'Save' }}</button>
      <a href="{{ route('bms.recurring-bills.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
