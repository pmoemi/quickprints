@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Log New Sale</div>
    <div class="page-subtitle">Record a sale and auto-create a job</div>
  </div>
  <a href="{{ route('bms.saleslog.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:780px;">
  <form method="POST" action="{{ route('bms.saleslog.store') }}">
    @csrf

    <div class="form-row cols-2">
      <div class="fld">
        <label>Date <span style="color:var(--red)">*</span></label>
        <input type="date" name="date" value="{{ old('date', $log->date ?? now()->toDateString()) }}" required>
      </div>
      <div class="fld">
        <label>Branch <span style="color:var(--red)">*</span></label>
        <select name="branch" required>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $log->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Client Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="client_name" value="{{ old('client_name', $log->client_name) }}" required placeholder="Client full name">
      </div>
      <div class="fld">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ old('phone', $log->phone) }}" placeholder="07xx xxx xxx">
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Job Description <span style="color:var(--red)">*</span></label>
        <input type="text" name="job_desc" value="{{ old('job_desc', $log->job_desc) }}" required placeholder="e.g. Vinyl Banner 3x1m">
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Category</label>
        <select name="category">
          <option value="">— Select —</option>
          @foreach(['Large Format','Signage','Vehicle Branding','Corporate','Promotional','Apparel','Fabrication','Events'] as $cat)
            <option value="{{ $cat }}" {{ old('category', $log->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Amount (KSh) <span style="color:var(--red)">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $log->amount) }}" required min="0" step="0.01" placeholder="0">
      </div>
      <div class="fld">
        <label>Payment Method</label>
        <select name="pay_method">
          @foreach(['Mpesa','Cash','Bank Transfer','Cheque','Credit'] as $m)
            <option value="{{ $m }}" {{ old('pay_method', $log->pay_method) === $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Payment Status</label>
        <select name="pay_status">
          <option value="pending" {{ old('pay_status', $log->pay_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="paid" {{ old('pay_status', $log->pay_status) === 'paid' ? 'selected' : '' }}>Paid</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="2" placeholder="Optional notes…">{{ old('notes', $log->notes) }}</textarea>
      </div>
    </div>

    <div class="alert alert-info" style="margin-bottom:16px;">
      A job will be auto-created from this sale entry.
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.saleslog.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Log Sale & Create Job</button>
    </div>
  </form>
</div>
@endsection
