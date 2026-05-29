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

<div class="grid-3" style="margin-bottom:20px;">
  <div class="stat-card">
    <div class="stat-label">Today's Total</div>
    <div class="stat-value green">{{ $bmsCurrency }} {{ number_format($summaries['today_total']) }}</div>
    <div class="stat-sub">{{ $summaries['today_count'] }} entries today</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">All Time Total</div>
    <div class="stat-value accent">{{ $bmsCurrency }} {{ number_format($summaries['all_total']) }}</div>
    <div class="stat-sub">{{ $summaries['all_count'] }} total entries</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Pending Payments</div>
    <div class="stat-value red">{{ $bmsCurrency }} {{ number_format($summaries['pending_total']) }}</div>
    <div class="stat-sub">{{ $summaries['pending_count'] }} pending</div>
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
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->job_desc }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->category ?? '—' }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->branch }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $log->salesRep?->name ?? $log->logged_by ?? '—' }}</span></td>
            <td><span class="mono">{{ $bmsCurrency }} {{ number_format($log->amount) }}</span></td>
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
              <form method="POST" action="{{ route('bms.saleslog.destroy', $log->id) }}" onsubmit="return confirm('Delete this entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="11">
              <div class="empty-state"><div class="empty-icon">🧾</div><p>No sales entries yet</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
