@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Daily Sales Log</div>
    <div class="page-subtitle">
      {{ $summaries['all_count'] }} entries
      @if($bmsCanAllBranches && $bmsBranch !== 'all')
        · {{ $bmsBranch }}
      @endif
    </div>
  </div>
  <a href="{{ route('bms.saleslog.create') }}" class="btn btn-primary">+ Log Sale</a>
</div>

{{-- Today's summary --}}
<div class="grid-4" style="margin-bottom:16px;">
  <div class="stat-card">
    <div class="stat-label">Today's Cash</div>
    <div class="stat-value green">{{ $bmsCurrency }} {{ number_format($summaries['today_cash']) }}</div>
    <div class="stat-sub">Cash received today</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Today's M-Pesa</div>
    <div class="stat-value accent">{{ $bmsCurrency }} {{ number_format($summaries['today_mpesa']) }}</div>
    <div class="stat-sub">M-Pesa received today</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Today's Billed</div>
    <div class="stat-value">{{ $bmsCurrency }} {{ number_format($summaries['today_total']) }}</div>
    <div class="stat-sub">{{ $summaries['today_count'] }} entries today</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending / Partial</div>
    <div class="stat-value red">{{ $bmsCurrency }} {{ number_format($summaries['pending_total']) }}</div>
    <div class="stat-sub">{{ $summaries['pending_count'] }} outstanding</div>
  </div>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Client</th>
          <th>Description</th>
          <th>Category</th>
          <th>Branch</th>
          <th>Logged By</th>
          <th>Amount</th>
          <th>Cash</th>
          <th>M-Pesa</th>
          <th>Method</th>
          <th>Status</th>
          <th>Job ID</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</td>
            <td style="font-weight:600;">{{ $log->client_name }}</td>
            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->job_desc }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->category ?? '—' }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->branch }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->salesRep?->name ?? $log->logged_by ?? '—' }}</span></td>
            <td><span class="mono">{{ $bmsCurrency }} {{ number_format($log->amount) }}</span></td>
            <td>
              @if(($log->cash_amount ?? 0) > 0)
                <span class="mono" style="font-size:12px;color:var(--green,#22c55e);">{{ number_format($log->cash_amount) }}</span>
              @else
                <span style="color:var(--text3);font-size:12px;">—</span>
              @endif
            </td>
            <td>
              @if(($log->mpesa_amount ?? 0) > 0)
                <span class="mono" style="font-size:12px;color:var(--accent);">{{ number_format($log->mpesa_amount) }}</span>
              @else
                <span style="color:var(--text3);font-size:12px;">—</span>
              @endif
            </td>
            <td><span class="badge badge-gray">{{ $log->pay_method ?? '—' }}</span></td>
            <td>
              @if($log->pay_status === 'paid')
                <span class="badge badge-green">Fully paid</span>
              @elseif($log->pay_status === 'partial')
                <span class="badge badge-orange">Partially paid</span>
              @else
                <span class="badge badge-orange">Pending</span>
              @endif
            </td>
            <td><span class="mono text-accent" style="font-size:11px;">{{ $log->job_id ?? '—' }}</span></td>
            <td>
              <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <a href="{{ route('bms.saleslog.edit-payment', $log->id) }}"
                   class="btn btn-secondary btn-sm"
                   title="Update payment for this sale">
                  {{ $log->pay_status === 'paid' ? 'Payment' : 'Mark Paid' }}
                </a>
                <form method="POST" action="{{ route('bms.saleslog.destroy', $log->id) }}" onsubmit="return confirm('Delete this entry?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="13">
              <div class="empty-state"><div class="empty-icon">🧾</div><p>No sales entries yet</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<style>
@media(max-width:900px) {
  .grid-4 { grid-template-columns: 1fr 1fr; }
}
@media(max-width:560px) {
  .grid-4 { grid-template-columns: 1fr; }
}
</style>
@endsection
