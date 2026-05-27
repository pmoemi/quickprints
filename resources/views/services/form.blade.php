@extends('layouts.bms')

@section('content')
@php $editing = $item->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Service' : 'Add Service' }}</div>
  </div>
  <a href="{{ route('bms.services.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:600px;">
  <form method="POST" action="{{ $editing ? route('bms.services.update', $item->id) : route('bms.services.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="fld" style="margin-bottom:16px;">
      <label>Category <span style="color:var(--red)">*</span></label>
      <input type="text" name="category" value="{{ old('category', $item->category) }}" required
             placeholder="e.g. Large Format Printing"
             list="category-list">
      <datalist id="category-list">
        @foreach($categories as $cat)
          <option value="{{ $cat }}">
        @endforeach
      </datalist>
      <small style="color:var(--text3);">Select existing or type a new category.</small>
    </div>

    <div class="fld" style="margin-bottom:16px;">
      <label>Service Name <span style="color:var(--red)">*</span></label>
      <input type="text" name="name" value="{{ old('name', $item->name) }}" required
             placeholder="e.g. Vinyl Banners">
    </div>

    <div class="fld" style="margin-bottom:24px;">
      <label>Sort Order</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0" placeholder="0" style="max-width:120px;">
      <small style="color:var(--text3);">Lower numbers appear first within a category.</small>
    </div>

    @if($errors->any())
      <div style="color:var(--red);margin-bottom:16px;font-size:13px;">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
      </div>
    @endif

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.services.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Service' : 'Add Service' }}</button>
    </div>
  </form>
</div>
@endsection
