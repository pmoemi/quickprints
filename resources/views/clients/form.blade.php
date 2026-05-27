@extends('layouts.bms')

@section('content')
@php $editing = $client->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Client' : 'New Client' }}</div>
    <div class="page-subtitle">{{ $editing ? $client->name : 'Add a new client' }}</div>
  </div>
  <a href="{{ route('bms.clients.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:720px;">
  <form method="POST" action="{{ $editing ? route('bms.clients.update', $client->id) : route('bms.clients.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Full Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" value="{{ old('name', $client->name) }}" required placeholder="Client full name">
      </div>
      <div class="fld">
        <label>Company</label>
        <input type="text" name="company" value="{{ old('company', $client->company) }}" placeholder="Company name">
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" placeholder="07xx xxx xxx">
      </div>
      <div class="fld">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $client->email) }}" placeholder="client@email.com">
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="">— Select —</option>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $client->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Additional notes…">{{ old('notes', $client->notes) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.clients.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Client' : 'Create Client' }}</button>
    </div>
  </form>
</div>
@endsection
