@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

@php
  $nb = $settings['numbering'] ?? [];
  $jobPrefix     = $nb['job_prefix']     ?? 'QP';
  $jobStart      = $nb['job_start']      ?? 10001;
  $jobPad        = $nb['job_pad']        ?? 5;
  $jobPerBranch  = !empty($nb['job_per_branch']);
  $quotePrefix   = $nb['quote_prefix']   ?? 'QT';
  $quotePad      = $nb['quote_pad']      ?? 4;
  $invoicePrefix = $nb['invoice_prefix'] ?? 'INV';
  $receiptPrefix = $nb['receipt_prefix'] ?? 'RCP';

  // Live examples
  $exJobNum  = max($jobStart, $nextJobNum ?? $jobStart);
  $exJobId   = $jobPrefix.'-'.str_pad($exJobNum, $jobPad, '0', STR_PAD_LEFT);
  $exQuoteId = $quotePrefix.'-'.str_pad($nextQuoteNum ?? 1, $quotePad, '0', STR_PAD_LEFT);
  $exInvRef  = $invoicePrefix.'-'.$exJobId;
  $exRcpRef  = $receiptPrefix.'-'.$exJobId;
@endphp

<form method="POST" action="{{ route('bms.settings.numbering.update') }}">
  @csrf @method('PUT')

  <div class="grid-2" style="gap:20px;">

    {{-- Job IDs --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Job IDs</div>
        <span style="font-size:11px;color:var(--text3);">Applies to all jobs system-wide</span>
      </div>

      <div class="alert alert-info" style="margin-bottom:16px;font-size:12px;">
        Job IDs are <strong>global</strong> — they increment across all branches to keep references unique.
        New jobs created after saving will use the updated format. Existing IDs are unchanged.
      </div>

      <div class="form-row cols-3">
        <div class="fld">
          <label>Prefix</label>
          <input type="text" name="numbering[job_prefix]" value="{{ old('numbering.job_prefix', $jobPrefix) }}"
                 maxlength="10" placeholder="QP" required oninput="updatePreviews()">
        </div>
        <div class="fld">
          <label>Starting Number</label>
          <input type="number" name="numbering[job_start]" value="{{ old('numbering.job_start', $jobStart) }}"
                 min="1" max="999999" required oninput="updatePreviews()">
          <div style="font-size:10px;color:var(--text3);margin-top:3px;">Used only if no jobs exist yet</div>
        </div>
        <div class="fld">
          <label>Zero-pad digits</label>
          <select name="numbering[job_pad]" oninput="updatePreviews()">
            @foreach([3,4,5,6] as $p)
              <option value="{{ $p }}" {{ (int)old('numbering.job_pad', $jobPad) === $p ? 'selected' : '' }}>{{ $p }} digits</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="fld" style="display:flex;align-items:center;gap:10px;">
          <label style="margin:0;display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="hidden" name="numbering[job_per_branch]" value="0">
            <input type="checkbox" name="numbering[job_per_branch]" value="1"
                   {{ $jobPerBranch ? 'checked' : '' }} onchange="updatePreviews()">
            <span>Add branch code to Job ID</span>
          </label>
          <span style="font-size:11px;color:var(--text3);">e.g. QP-WL-10001 for Westlands</span>
        </div>
      </div>

      <div class="card" style="background:var(--bg3);margin-top:4px;">
        <div style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Preview</div>
        <div style="display:flex;flex-direction:column;gap:6px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:var(--text2);">Next Job ID</span>
            <code id="prev-job" style="font-family:var(--mono);font-size:14px;font-weight:700;color:var(--accent);">{{ $exJobId }}</code>
          </div>
        </div>
      </div>
    </div>

    {{-- Quotes --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Quote Numbers</div>
      </div>

      <div class="form-row cols-2">
        <div class="fld">
          <label>Prefix</label>
          <input type="text" name="numbering[quote_prefix]" value="{{ old('numbering.quote_prefix', $quotePrefix) }}"
                 maxlength="10" placeholder="QT" required oninput="updatePreviews()">
        </div>
        <div class="fld">
          <label>Zero-pad digits</label>
          <select name="numbering[quote_pad]" oninput="updatePreviews()">
            @foreach([3,4,5,6] as $p)
              <option value="{{ $p }}" {{ (int)old('numbering.quote_pad', $quotePad) === $p ? 'selected' : '' }}>{{ $p }} digits</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="card" style="background:var(--bg3);margin-top:4px;">
        <div style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Preview</div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:12px;color:var(--text2);">Next Quote</span>
          <code id="prev-quote" style="font-family:var(--mono);font-size:14px;font-weight:700;color:var(--accent);">{{ $exQuoteId }}</code>
        </div>
      </div>
    </div>

    {{-- Invoices & Receipts --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Invoice &amp; Receipt Labels</div>
        <span style="font-size:11px;color:var(--text3);">Display prefixes on printed documents</span>
      </div>

      <div class="alert alert-info" style="margin-bottom:16px;font-size:12px;">
        Invoices and Receipts are generated from Jobs — they share the same Job ID.
        The prefix here only affects the document heading/reference label.
      </div>

      <div class="form-row cols-2">
        <div class="fld">
          <label>Invoice Prefix</label>
          <input type="text" name="numbering[invoice_prefix]" value="{{ old('numbering.invoice_prefix', $invoicePrefix) }}"
                 maxlength="10" placeholder="INV" oninput="updatePreviews()">
        </div>
        <div class="fld">
          <label>Receipt Prefix</label>
          <input type="text" name="numbering[receipt_prefix]" value="{{ old('numbering.receipt_prefix', $receiptPrefix) }}"
                 maxlength="10" placeholder="RCP" oninput="updatePreviews()">
        </div>
      </div>

      <div class="card" style="background:var(--bg3);margin-top:4px;">
        <div style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Preview</div>
        <div style="display:flex;flex-direction:column;gap:6px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:var(--text2);">Invoice Ref</span>
            <code id="prev-inv" style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent);">{{ $exInvRef }}</code>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:var(--text2);">Receipt Ref</span>
            <code id="prev-rcp" style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--accent);">{{ $exRcpRef }}</code>
          </div>
        </div>
      </div>
    </div>

    {{-- Current counters info --}}
    <div class="card">
      <div class="card-header"><div class="card-title">Current Counters</div></div>
      <div class="activity-item">
        <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;width:120px;flex-shrink:0;">Next Job #</div>
        <code style="font-family:var(--mono);">{{ $exJobId }}</code>
      </div>
      <div class="activity-item">
        <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;width:120px;flex-shrink:0;">Next Quote #</div>
        <code style="font-family:var(--mono);">{{ $exQuoteId }}</code>
      </div>
      <div class="activity-item">
        <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;width:120px;flex-shrink:0;">Total Jobs</div>
        <span style="font-size:13px;">{{ $totalJobs }}</span>
      </div>
      <div class="activity-item">
        <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;width:120px;flex-shrink:0;">Total Quotes</div>
        <span style="font-size:13px;">{{ $totalQuotes }}</span>
      </div>
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);font-size:11px;color:var(--text3);">
        Counters are computed live from the database. Changing the prefix or padding does not renumber existing records.
      </div>
    </div>

  </div>

  <div style="margin-top:20px;">
    <button type="submit" class="btn btn-primary">Save Numbering Settings</button>
  </div>
</form>

<script>
const nextJob   = {{ $exJobNum }};
const nextQuote = {{ $nextQuoteNum ?? 1 }};

function pad(n, width) {
  return String(n).padStart(width, '0');
}

function updatePreviews() {
  const jobPrefix  = document.querySelector('[name="numbering[job_prefix]"]')?.value || 'QP';
  const jobPad     = parseInt(document.querySelector('[name="numbering[job_pad]"]')?.value || 5);
  const perBranch  = document.querySelector('[name="numbering[job_per_branch]"][type=checkbox]')?.checked;
  const invPrefix  = document.querySelector('[name="numbering[invoice_prefix]"]')?.value || 'INV';
  const rcpPrefix  = document.querySelector('[name="numbering[receipt_prefix]"]')?.value || 'RCP';
  const qPrefix    = document.querySelector('[name="numbering[quote_prefix]"]')?.value || 'QT';
  const qPad       = parseInt(document.querySelector('[name="numbering[quote_pad]"]')?.value || 4);

  const branchSuffix = perBranch ? '-XX' : '';
  const jobId  = jobPrefix + branchSuffix + '-' + pad(nextJob, jobPad);
  const quoteId = qPrefix + '-' + pad(nextQuote, qPad);

  const el = id => document.getElementById(id);
  if (el('prev-job'))   el('prev-job').textContent   = jobId;
  if (el('prev-quote')) el('prev-quote').textContent = quoteId;
  if (el('prev-inv'))   el('prev-inv').textContent   = invPrefix + '-' + jobId;
  if (el('prev-rcp'))   el('prev-rcp').textContent   = rcpPrefix + '-' + jobId;
}
</script>
@endsection
