@extends('layouts.bms')

@section('content')
@php $editing = $asset->exists; @endphp

<div class="page-header">
  <div><div class="page-title">{{ $editing ? 'Edit Asset' : 'Add Asset' }}</div></div>
  <a href="{{ route('bms.assets.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:700px;">
  <form method="POST" action="{{ $editing ? route('bms.assets.update', $asset->id) : route('bms.assets.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Asset Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="{{ old('name', $asset->name) }}" required placeholder="e.g. HP Latex 365 Printer">
      </div>
      <div class="fld">
        <label>Category</label>
        <select name="category">
          @foreach(['Machine','Vehicle','Computer/IT','Furniture','Other'] as $c)
            <option value="{{ $c }}" {{ old('category', $asset->category) === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Purchase Cost (KSh)</label>
        <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Current Value (KSh)</label>
        <input type="number" name="current_value" value="{{ old('current_value', $asset->current_value) }}" min="0" step="0.01" placeholder="0">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Condition</label>
        <select name="condition_status">
          @foreach(['Good','Fair','Poor','Under Maintenance'] as $c)
            <option value="{{ $c }}" {{ old('condition_status', $asset->condition_status ?? 'Good') === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="">— Select —</option>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $asset->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.assets.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Asset' : 'Add Asset' }}</button>
    </div>
  </form>
</div>
@endsection
