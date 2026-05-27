@extends('layouts.bms')

@section('content')
@php $editing = $job->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Job '.$job->id : 'New Job' }}</div>
    <div class="page-subtitle">{{ $editing ? 'Update job details' : 'Create a new print job' }}</div>
  </div>
  <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:900px;">
  <form method="POST" action="{{ $editing ? route('bms.jobs.update', $job->id) : route('bms.jobs.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Client</label>
        <select name="client_id">
          <option value="">— Select client —</option>
          @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ old('client_id', $job->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Branch <span style="color:var(--red)">*</span></label>
        <select name="branch" required>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $job->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Job Title / Description <span style="color:var(--red)">*</span></label>
        <input type="text" name="title" value="{{ old('title', $job->title) }}" placeholder="e.g. Vinyl Banner 3x1m" required>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Category</label>
        <select name="category">
          <option value="">— Select —</option>
          @foreach(['Large Format','Signage','Vehicle Branding','Corporate','Promotional','Apparel','Fabrication','Events','Photography','Digital'] as $cat)
            <option value="{{ $cat }}" {{ old('category', $job->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Amount (KSh)</label>
        <input type="number" name="amount" value="{{ old('amount', $job->amount) }}" placeholder="0" min="0" step="0.01">
      </div>
      <div class="fld">
        <label>Priority</label>
        <select name="priority">
          @foreach(['low','medium','high'] as $p)
            <option value="{{ $p }}" {{ old('priority', $job->priority ?? 'medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Stage</label>
        <select name="stage">
          @foreach(['waiting','designing','approval','printing','fabrication','ready','installed','paid'] as $s)
            <option value="{{ $s }}" {{ old('stage', $job->stage ?? 'waiting') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Assigned Designer</label>
        <select name="designer_id">
          <option value="">None</option>
          @foreach($staff as $s)
            <option value="{{ $s->id }}" {{ old('designer_id', $job->designer_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Deadline</label>
        <input type="date" name="deadline" value="{{ old('deadline', $job->deadline?->format('Y-m-d')) }}">
      </div>
    </div>

    @if($editing)
    <div class="form-row cols-2">
      <div class="fld">
        <label>Payment Status</label>
        <select name="paid">
          <option value="0" {{ !$job->paid ? 'selected' : '' }}>Unpaid</option>
          <option value="1" {{ $job->paid ? 'selected' : '' }}>Paid</option>
        </select>
      </div>
    </div>
    @endif

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Additional notes…">{{ old('notes', $job->notes) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
      <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Job' : 'Create Job' }}</button>
    </div>
  </form>
</div>
@endsection
