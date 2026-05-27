@extends('layouts.bms')

@section('content')
@php $editing = $item->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Item' : 'Add Inventory Item' }}</div>
  </div>
  <a href="{{ route('bms.inventory.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:700px;">
  <form method="POST" action="{{ $editing ? route('bms.inventory.update', $item->id) : route('bms.inventory.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Item Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" required placeholder="e.g. Vinyl Roll 3.2m">
      </div>
      <div class="fld">
        <label>Category</label>
        <select name="cat">
          <option value="">— Select —</option>
          @foreach(['Media','Ink','Paper','Metal','Acrylic','Fabric','Chemicals','Other'] as $c)
            <option value="{{ $c }}" {{ old('cat', $item->cat ?? $item->category) === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Quantity</label>
        <input type="number" name="qty" value="{{ old('qty', $item->qty) }}" min="0" placeholder="0">
      </div>
      <div class="fld">
        <label>Unit</label>
        <select name="unit">
          @foreach(['roll','litre','pack','sheet','pcs','kg','m','sqm','bottle'] as $u)
            <option value="{{ $u }}" {{ old('unit', $item->unit) === $u ? 'selected' : '' }}>{{ $u }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level ?? 2) }}" min="0" placeholder="2">
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Unit Cost ({{ $bmsCurrency }})</label>
        <input type="number" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $item->branch ?? 'all') === $br ? 'selected' : '' }}>{{ $br === 'all' ? 'All Branches' : $br }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.inventory.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Item' : 'Add Item' }}</button>
    </div>
  </form>
</div>
@endsection
