@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Update Payment</div>
    <div class="page-subtitle">{{ $log->client_name }} · {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</div>
  </div>
  <a href="{{ route('bms.saleslog.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:600px;">

  {{-- Sale summary (read-only) --}}
  <div style="background:var(--bg3);border-radius:var(--radius);padding:14px 16px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
      <div>
        <div style="font-weight:700;font-size:14px;">{{ $log->client_name }}</div>
        <div style="font-size:12px;color:var(--text2);margin-top:2px;">{{ $log->job_desc }}</div>
        @if($log->job_id)
          <div style="font-size:11px;color:var(--text3);margin-top:2px;font-family:var(--mono);">{{ $log->job_id }}</div>
        @endif
      </div>
      <div style="text-align:right;">
        <div style="font-size:18px;font-weight:700;color:var(--text);">{{ $bmsCurrency }} {{ number_format($log->amount) }}</div>
        <div style="font-size:11px;color:var(--text3);">Total billed</div>
      </div>
    </div>
    <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;gap:16px;flex-wrap:wrap;">
      <div style="font-size:12px;">
        <span style="color:var(--text3);">Current status:</span>
        @if($log->pay_status === 'paid')
          <span class="badge badge-green" style="margin-left:4px;">Fully paid</span>
        @elseif($log->pay_status === 'partial')
          <span class="badge badge-orange" style="margin-left:4px;">Partially paid</span>
        @else
          <span class="badge badge-orange" style="margin-left:4px;">Pending</span>
        @endif
      </div>
      @if($log->pay_method)
        <div style="font-size:12px;color:var(--text2);">Method: <strong>{{ $log->pay_method }}</strong></div>
      @endif
      @if($log->cash_amount > 0 || $log->mpesa_amount > 0)
        <div style="font-size:12px;color:var(--text2);">
          Cash: {{ $bmsCurrency }} {{ number_format($log->cash_amount) }} ·
          M-Pesa: {{ $bmsCurrency }} {{ number_format($log->mpesa_amount) }}
        </div>
      @endif
    </div>
  </div>

  <form method="POST" action="{{ route('bms.saleslog.update-payment', $log->id) }}">
    @csrf
    @method('PATCH')

    <div style="border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;margin-bottom:16px;background:var(--bg3);">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);margin-bottom:12px;">Payment Received</div>

      <div class="form-row cols-2" style="margin-bottom:0;">
        <div class="fld" style="margin-bottom:10px;">
          <label>Cash ({{ $bmsCurrency }})</label>
          <input type="number" name="cash_amount" id="ep-cash"
                 value="{{ old('cash_amount', $log->cash_amount ?? 0) }}" min="0" step="0.01"
                 oninput="epSync()">
        </div>
        <div class="fld" style="margin-bottom:10px;">
          <label>M-Pesa ({{ $bmsCurrency }})</label>
          <input type="number" name="mpesa_amount" id="ep-mpesa"
                 value="{{ old('mpesa_amount', $log->mpesa_amount ?? 0) }}" min="0" step="0.01"
                 oninput="epSync()">
        </div>
      </div>

      <div id="ep-auto-info" style="font-size:12px;color:var(--text2);margin-bottom:10px;display:none;">
        Method: <strong id="ep-method-label">—</strong> · Status: <strong id="ep-status-label">—</strong>
      </div>

      <div id="ep-other-section" class="form-row cols-2" style="margin-bottom:0;margin-top:4px;">
        <div class="fld" style="margin-bottom:0;">
          <label>Other Method</label>
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
          <select name="pay_status">
            <option value="pending"  {{ old('pay_status', $log->pay_status) === 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="partial"  {{ old('pay_status', $log->pay_status) === 'partial'  ? 'selected' : '' }}>Partially paid</option>
            <option value="paid"     {{ old('pay_status', $log->pay_status) === 'paid'     ? 'selected' : '' }}>Fully paid</option>
          </select>
        </div>
      </div>
    </div>

    <div class="fld" style="margin-bottom:16px;">
      <label>Notes</label>
      <textarea name="notes" rows="2" placeholder="Optional notes…">{{ old('notes', $log->notes) }}</textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.saleslog.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Payment</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
const _epAmount = {{ (float) $log->amount }};

function epSync() {
  const cash  = parseFloat(document.getElementById('ep-cash').value) || 0;
  const mpesa = parseFloat(document.getElementById('ep-mpesa').value) || 0;
  const total = cash + mpesa;
  const other = document.getElementById('ep-other-section');
  const info  = document.getElementById('ep-auto-info');

  if (total > 0) {
    other.style.display = 'none';
    info.style.display  = 'block';
    const method = cash > 0 && mpesa > 0 ? 'Mixed (Cash + M-Pesa)' : (cash > 0 ? 'Cash' : 'M-Pesa');
    const status = total >= _epAmount ? 'Fully paid' : 'Partially paid';
    document.getElementById('ep-method-label').textContent = method;
    document.getElementById('ep-status-label').textContent = status;
  } else {
    other.style.display = '';
    info.style.display  = 'none';
  }
}
epSync();
</script>
@endpush
@endsection
