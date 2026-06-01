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
        @if(count($branches) === 1)
          <input type="hidden" name="branch" value="{{ $branches[0] }}">
          <input type="text" value="{{ $branches[0] }}" disabled style="opacity:.85;cursor:not-allowed;">
        @else
          <select name="branch" required>
            @foreach($branches as $br)
              <option value="{{ $br }}" {{ old('branch', $log->branch ?? $branches[0] ?? null) === $br ? 'selected' : '' }}>{{ $br }}</option>
            @endforeach
          </select>
        @endif
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Client <span style="color:var(--red)">*</span></label>
        @if(!empty($clients) && count($clients) > 0)
          <select name="client_id">
            <option value="">— New client —</option>
            @foreach($clients as $c)
              <option value="{{ $c->id }}" {{ (string) old('client_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }} @if($c->company) ({{ $c->company }}) @endif</option>
            @endforeach
          </select>
          <div style="font-size:12px;color:var(--text2);margin-top:6px;">Or enter a new client below.</div>
          <div style="margin-top:8px;">
            <input type="text" name="client_name" value="{{ old('client_name', $log->client_name) }}" placeholder="New client full name">
          </div>
        @else
          <input type="text" name="client_name" value="{{ old('client_name', $log->client_name) }}" required placeholder="Client full name">
        @endif
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

    <div class="form-row cols-2">
      <div class="fld">
        <label>Category</label>
        <select name="category">
          <option value="">— Select —</option>
          @foreach($bmsJobCategories as $cat)
            <option value="{{ $cat }}" {{ old('category', $log->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Amount ({{ $bmsCurrency }}) <span style="color:var(--red)">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $log->amount) }}" required min="0" step="0.01" placeholder="0">
      </div>
    </div>

    {{-- Payment Section --}}
    <div style="border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;margin-bottom:14px;background:var(--bg3);">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);margin-bottom:12px;">Payment Received</div>

      <div class="form-row cols-2" style="margin-bottom:0;">
        <div class="fld" style="margin-bottom:10px;">
          <label>Cash ({{ $bmsCurrency }})</label>
          <input type="number" name="cash_amount" id="cash-inp"
                 value="{{ old('cash_amount', 0) }}" min="0" step="0.01" placeholder="0"
                 oninput="syncPayMode()">
        </div>
        <div class="fld" style="margin-bottom:10px;">
          <label>M-Pesa ({{ $bmsCurrency }})</label>
          <input type="number" name="mpesa_amount" id="mpesa-inp"
                 value="{{ old('mpesa_amount', 0) }}" min="0" step="0.01" placeholder="0"
                 oninput="syncPayMode()">
        </div>
      </div>

      {{-- Auto-computed status indicator --}}
      <div id="pay-auto-info" style="font-size:12px;color:var(--text2);margin-bottom:10px;display:none;">
        Method: <strong id="pay-method-label">—</strong> · Status: <strong id="pay-status-label">—</strong>
      </div>

      {{-- Fallback: other payment methods (shown only when no cash/mpesa entered) --}}
      <div id="pay-other-section" class="form-row cols-2" style="margin-bottom:0;margin-top:4px;">
        <div class="fld" style="margin-bottom:0;">
          <label>Other Method <span style="font-size:10px;color:var(--text3);">(Bank Transfer, Cheque…)</span></label>
          <select name="pay_method">
            <option value="">— None / Pending —</option>
            @foreach($bmsPaymentMethods as $m)
              @if(!in_array($m, ['Cash', 'Mpesa', 'M-Pesa']))
                <option value="{{ $m }}" {{ old('pay_method', $log->pay_method ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
              @endif
            @endforeach
          </select>
        </div>
        <div class="fld" style="margin-bottom:0;">
          <label>Payment Status</label>
          <select name="pay_status" id="pay-status-select">
            <option value="pending" {{ old('pay_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="partial" {{ old('pay_status', '') === 'partial' ? 'selected' : '' }}>Partially paid</option>
            <option value="paid"    {{ old('pay_status', '') === 'paid' ? 'selected' : '' }}>Fully paid</option>
          </select>
        </div>
      </div>
    </div>

    <div class="form-row cols-2">
      <div class="fld">
        <label>Assigned Designer <span style="color:var(--red)">*</span></label>
        <select name="designer_id" required>
          <option value="">— Select designer —</option>
          @foreach($designers as $d)
            <option value="{{ $d->id }}" {{ old('designer_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
          @endforeach
        </select>
        @error('designer_id')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>&nbsp;</label>
        <div style="padding:10px 12px;background:var(--bg3);border-radius:var(--radius);font-size:12px;color:var(--text2);margin-top:2px;">
          A job will be auto-created and assigned to the designer.
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="2" placeholder="Optional notes…">{{ old('notes', $log->notes) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.saleslog.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Log Sale & Create Job</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function syncPayMode() {
  const cash  = parseFloat(document.getElementById('cash-inp').value) || 0;
  const mpesa = parseFloat(document.getElementById('mpesa-inp').value) || 0;
  const total = cash + mpesa;
  const other = document.getElementById('pay-other-section');
  const info  = document.getElementById('pay-auto-info');

  if (total > 0) {
    other.style.display = 'none';
    info.style.display  = 'block';
    const amount = parseFloat(document.querySelector('[name=amount]').value) || 0;
    const method = cash > 0 && mpesa > 0 ? 'Mixed (Cash + M-Pesa)' : (cash > 0 ? 'Cash' : 'M-Pesa');
    const status = total >= amount && amount > 0 ? 'Fully paid' : (total > 0 ? 'Partially paid' : 'Pending');
    document.getElementById('pay-method-label').textContent = method;
    document.getElementById('pay-status-label').textContent = status;
  } else {
    other.style.display = '';
    info.style.display  = 'none';
  }
}
syncPayMode();
</script>
@endpush
@endsection
