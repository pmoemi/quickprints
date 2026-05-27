@extends('layouts.bms')

@section('content')
@php $editing = $item->exists; @endphp

<div class="page-header">
  <div><div class="page-title">{{ $editing ? 'Edit Supplier' : 'Add Supplier' }}</div></div>
  <a href="{{ route('bms.suppliers.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:680px;">
  <form method="POST" action="{{ $editing ? route('bms.suppliers.update', $item->id) : route('bms.suppliers.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Supplier Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" required placeholder="e.g. Apex Inks Kenya">
      </div>
      <div class="fld">
        <label>Contact Person</label>
        <input type="text" name="contact" value="{{ old('contact', $item->contact) }}" placeholder="Contact name">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $item->phone) }}" placeholder="07xx xxx xxx">
      </div>
      <div class="fld">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $item->email) }}" placeholder="supplier@email.com">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Category</label>
        <select name="category">
          <option value="">— Select —</option>
          @foreach(['Ink & Media','Hardware','Metal & Fabrication','Paper & Board','Apparel','General','IT & Software'] as $c)
            <option value="{{ $c }}" {{ old('category', $item->category) === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Payment Terms</label>
        <select name="payment_terms">
          @foreach(['COD','7 days','14 days','30 days','60 days'] as $t)
            <option value="{{ $t }}" {{ old('payment_terms', $item->payment_terms) === $t ? 'selected' : '' }}>{{ $t }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Address</label>
        <input type="text" name="address" value="{{ old('address', $item->address) }}" placeholder="Physical / postal address">
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.suppliers.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Supplier' : 'Add Supplier' }}</button>
    </div>
  </form>
</div>
@endsection
