@extends('layouts.bms')

@section('content')
@php $editing = $lead->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Lead' : 'New Lead' }}</div>
  </div>
  <a href="{{ route('bms.leads.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:720px;">
  <form method="POST" action="{{ $editing ? route('bms.leads.update', $lead->id) : route('bms.leads.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Client Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="client_name" value="{{ old('client_name', $lead->client_name) }}" required placeholder="Lead / client name">
      </div>
      <div class="fld">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $lead->phone) }}" placeholder="07xx xxx xxx">
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Service Interested In</label>
        <select name="service">
          <option value="">— Select —</option>
          @foreach(['Large Format','Signage','Vehicle Branding','Corporate','Promotional','Apparel','Fabrication','Events'] as $s)
            <option value="{{ $s }}" {{ old('service', $lead->service) === $s ? 'selected' : '' }}>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Estimated Value (KSh)</label>
        <input type="number" name="value" value="{{ old('value', $lead->value) }}" min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          @foreach(['new','contacted','qualified','proposal','won','lost'] as $s)
            <option value="{{ $s }}" {{ old('status', $lead->status ?? 'new') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Branch</label>
        <select name="branch">
          <option value="">— Select —</option>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $lead->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Assigned To</label>
        <select name="assigned_to">
          <option value="">— None —</option>
          @foreach($staff as $s)
            <option value="{{ $s->name }}" {{ old('assigned_to', $lead->assigned_to) === $s->name ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Follow Up Date</label>
        <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $lead->follow_up_date) }}">
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.leads.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Lead' : 'Create Lead' }}</button>
    </div>
  </form>
</div>
@endsection
