@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Cash Reconciliation</div>
    <div class="page-subtitle">{{ $branch ? "Branch: {$branch}" : 'All Branches' }}</div>
  </div>
</div>

<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Cash In</div>
    <div class="stat-value" style="color:var(--green);">KES {{ number_format($cashIn) }}</div>
    <div class="stat-sub">Sales + Paid Jobs</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Cash Out</div>
    <div class="stat-value" style="color:var(--red);">KES {{ number_format($cashOut) }}</div>
    <div class="stat-sub">Petty Cash Out + OpEx</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Net Position</div>
    @php $net = $cashIn - $cashOut; @endphp
    <div class="stat-value" style="color:{{ $net >= 0 ? 'var(--green)' : 'var(--red)' }};">KES {{ number_format($net) }}</div>
    <div class="stat-sub">Cash In – Cash Out</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Petty Cash Balance</div>
    <div class="stat-value">KES {{ number_format($pettyBalance) }}</div>
    <div class="stat-sub">Running balance</div>
  </div>
</div>

<div class="card">
  <div style="font-size:13px;color:var(--text2);line-height:1.8;">
    <p>This reconciliation draws from:</p>
    <ul style="margin-left:20px;margin-top:8px;">
      <li><strong>Cash In:</strong> Sales Log entries + Paid print jobs</li>
      <li><strong>Cash Out:</strong> Petty Cash (out) + Operating Expenses</li>
      <li><strong>Petty Cash Balance:</strong> Net of all petty cash entries</li>
    </ul>
    <p style="margin-top:12px;color:var(--text3);font-size:12px;">For a detailed breakdown, navigate to the Sales Log, Petty Cash, and OpEx sections.</p>
  </div>
</div>
@endsection
