@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">General Ledger</div>
    <div class="page-subtitle">{{ $entries->count() }} journal entries</div>
  </div>
  <a href="{{ route('bms.ledger.create') }}" class="btn btn-primary">+ Journal Entry</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Entry ID</th><th>Date</th><th>Ref</th><th>Description</th><th>Debit Acct</th><th>Debit</th><th>Credit Acct</th><th>Credit</th><th>Posted By</th></tr>
      </thead>
      <tbody>
        @forelse($entries as $entry)
          @php
            $lines = $entry->lines ?? [];
            $debitLine = collect($lines)->firstWhere('debit', '>', 0) ?? [];
            $creditLine = collect($lines)->firstWhere('credit', '>', 0) ?? [];
          @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $entry->entry_id }}</td>
            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
            <td style="font-size:12px;color:var(--text3);">{{ $entry->ref ?? '—' }}</td>
            <td style="font-size:13px;">{{ $entry->description }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $debitLine['account'] ?? '—' }}</td>
            <td style="font-size:12px;color:var(--red);">{{ isset($debitLine['debit']) ? number_format($debitLine['debit']) : '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $creditLine['account'] ?? '—' }}</td>
            <td style="font-size:12px;color:var(--green);">{{ isset($creditLine['credit']) ? number_format($creditLine['credit']) : '—' }}</td>
            <td style="font-size:12px;color:var(--text3);">{{ $entry->posted_by ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📒</div><p>No journal entries</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
