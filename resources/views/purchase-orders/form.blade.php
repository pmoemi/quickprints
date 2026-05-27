@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">New Purchase Order</div>
    <div class="page-subtitle">Submit a purchase request</div>
  </div>
  <a href="{{ route('bms.purchase-orders.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:640px;">
  <div class="card-header"><div class="card-title">Purchase Order Details</div></div>
  <form method="POST" action="{{ route('bms.purchase-orders.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Date</label>
        <input type="date" name="date" value="{{ old('date', $item->date) }}" required>
        @error('date')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          @foreach(['pending','approved','rejected'] as $s)
            <option value="{{ $s }}" {{ old('status', $item->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Supplier</label>
        <input type="text" name="supplier" value="{{ old('supplier', $item->supplier) }}" required placeholder="Supplier name">
        @error('supplier')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Description</label>
        <input type="text" name="description" value="{{ old('description', $item->description) }}" placeholder="Items / purpose">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount (KES)</label>
        <input type="number" name="amount" value="{{ old('amount', $item->amount) }}" min="0" step="0.01" required>
        @error('amount')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
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
      <button type="submit" class="btn btn-primary">Submit PO</button>
      <a href="{{ route('bms.purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
