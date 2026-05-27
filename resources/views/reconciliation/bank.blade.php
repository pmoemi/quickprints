@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Bank Reconciliation</div>
    <div class="page-subtitle">{{ $unmatched }} unmatched line(s)</div>
  </div>
</div>

<div class="card" style="max-width:600px;margin-bottom:20px;">
  <div class="card-header"><div class="card-title">Add Statement Line</div></div>
  <form method="POST" action="{{ route('bms.bank-recon.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Date</label>
        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
      </div>
      <div class="fld">
        <label>Type</label>
        <select name="type" required>
          <option value="credit" {{ old('type') === 'credit' ? 'selected' : '' }}>Credit (In)</option>
          <option value="debit" {{ old('type') === 'debit' ? 'selected' : '' }}>Debit (Out)</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Description</label>
        <input type="text" name="description" value="{{ old('description') }}" required>
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Amount</label>
        <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required>
      </div>
      <div class="fld">
        <label>Bank</label>
        <input type="text" name="bank" value="{{ old('bank') }}" placeholder="KCB, Equity...">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Add Line</button>
  </form>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Date</th><th>Description</th><th>Bank</th><th>Type</th><th>Amount</th><th>Status</th><th>Match Ref</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($lines as $line)
          <tr>
            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($line->date)->format('d M Y') }}</td>
            <td style="font-size:13px;">{{ $line->description }}</td>
            <td style="font-size:12px;color:var(--text3);">{{ $line->bank ?? '—' }}</td>
            <td>
              <span class="badge {{ $line->type === 'credit' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($line->type) }}</span>
            </td>
            <td style="font-weight:600;color:{{ $line->type === 'credit' ? 'var(--green)' : 'var(--red)' }};">
              KES {{ number_format($line->amount) }}
            </td>
            <td>
              @if($line->matched)
                <span class="badge badge-green">Matched</span>
              @else
                <span class="badge badge-gray">Unmatched</span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--text3);">{{ $line->matched_ref ?? '—' }}</td>
            <td>
              @if(!$line->matched)
                <form method="POST" action="{{ route('bms.bank-recon.match', $line->id) }}" style="display:flex;gap:4px;">
                  @csrf @method('PATCH')
                  <input type="text" name="matched_ref" style="height:28px;font-size:11px;width:100px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:4px 8px;color:var(--text);" placeholder="Ref #" required>
                  <button type="submit" class="btn btn-success btn-sm">Match</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🏦</div><p>No bank statement lines</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
