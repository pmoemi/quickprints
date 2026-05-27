@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Payments</div>
    <div class="page-subtitle">Track outstanding & received payments</div>
  </div>
</div>

@php
  $unpaid = $jobs->filter(fn ($j) => $j->paymentStatus() !== 'full');
  $paid   = $jobs->filter(fn ($j) => $j->amountPaid() > 0);
  $totalOwed = $unpaid->sum(fn ($j) => $j->balanceDue());
  $totalPaid = $jobs->sum(fn ($j) => $j->amountPaid());
@endphp

<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Outstanding</div>
    <div class="stat-value">KES {{ number_format($totalOwed) }}</div>
    <div class="stat-sub">{{ $unpaid->count() }} job(s)</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Collected</div>
    <div class="stat-value" style="color:var(--green);">KES {{ number_format($totalPaid) }}</div>
    <div class="stat-sub">{{ $paid->count() }} job(s)</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Revenue</div>
    <div class="stat-value">KES {{ number_format($totalOwed + $totalPaid) }}</div>
    <div class="stat-sub">All time</div>
  </div>
</div>

<div class="card">
  <div style="font-size:13px;font-weight:600;margin-bottom:12px;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;">Outstanding Payments</div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Job ID</th><th>Title</th><th>Client</th><th>Branch</th><th>Stage</th><th>Status</th><th>Balance</th><th>Deadline</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($unpaid as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $job->id }}</td>
            <td style="font-weight:600;">{{ $job->title }}</td>
            <td>{{ $client?->name ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $job->branch }}</td>
            <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
            <td>@include('partials.job-payment-status', ['job' => $job])</td>
            <td style="font-weight:600;">KES {{ number_format($job->balanceDue()) }}</td>
            <td style="font-size:12px;color:var(--yellow);">{{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.jobs.show', $job->id) }}" class="btn btn-secondary btn-sm">View</a>
                <a href="{{ route('bms.jobs.invoice', $job->id) }}?return={{ urlencode(url()->current()) }}" class="btn btn-secondary btn-sm">Invoice</a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state" style="padding:20px 0;"><div class="empty-icon">💰</div><p>No outstanding payments</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card" style="margin-top:20px;">
  <div style="font-size:13px;font-weight:600;margin-bottom:12px;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;">Recently Paid</div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Job ID</th><th>Title</th><th>Client</th><th>Branch</th><th>Amount</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($paid->take(30) as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $job->id }}</td>
            <td style="font-weight:600;">{{ $job->title }}</td>
            <td>{{ $client?->name ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $job->branch }}</td>
            <td style="font-weight:600;color:var(--green);">KES {{ number_format($job->amountPaid()) }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.jobs.show', $job->id) }}" class="btn btn-secondary btn-sm">View</a>
                <a href="{{ route('bms.jobs.invoice', $job->id) }}?return={{ urlencode(url()->current()) }}" class="btn btn-secondary btn-sm">Invoice</a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state" style="padding:20px 0;"><p>No paid jobs</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
