@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Sales Targets</div>
    <div class="page-subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
  </div>
  <form method="GET" action="{{ route('bms.sales-targets.index') }}" style="display:flex;gap:8px;align-items:center;">
    <input type="month" name="month" class="filter-select" value="{{ $month }}">
    <button type="submit" class="btn btn-secondary">Go</button>
  </form>
</div>

@php
  $pct = $target > 0 ? min(round($actual / $target * 100), 100) : 0;
  $remaining = max($target - $actual, 0);
  $overachieved = $actual > $target;
@endphp

<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Target</div>
    <div class="stat-value">KES {{ number_format($target) }}</div>
    <div class="stat-sub">Monthly goal</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Actual Sales</div>
    <div class="stat-value" style="color:{{ $overachieved ? 'var(--green)' : 'var(--accent)' }};">KES {{ number_format($actual) }}</div>
    <div class="stat-sub">{{ $pct }}% of target</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">{{ $overachieved ? 'Over by' : 'Remaining' }}</div>
    <div class="stat-value" style="color:{{ $overachieved ? 'var(--green)' : 'var(--yellow)' }};">
      KES {{ $overachieved ? number_format($actual - $target) : number_format($remaining) }}
    </div>
    <div class="stat-sub">{{ $overachieved ? 'Exceeded target!' : 'To reach target' }}</div>
  </div>
</div>

<div class="card" style="max-width:600px;margin-bottom:20px;">
  <div class="card-header"><div class="card-title">Progress</div></div>
  <div style="background:var(--bg3);border-radius:6px;height:20px;overflow:hidden;">
    <div style="background:{{ $overachieved ? 'var(--green)' : 'var(--accent)' }};height:100%;width:{{ $pct }}%;transition:width .5s;border-radius:6px;"></div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text3);margin-top:6px;">
    <span>KES 0</span>
    <span style="font-weight:700;color:{{ $overachieved ? 'var(--green)' : 'var(--accent)' }};">{{ $pct }}%</span>
    <span>KES {{ number_format($target) }}</span>
  </div>
</div>

<div class="card" style="max-width:400px;">
  <div class="card-header"><div class="card-title">Set Target</div></div>
  <form method="POST" action="{{ route('bms.sales-targets.update') }}">
    @csrf @method('PATCH')
    <input type="hidden" name="month" value="{{ $month }}">
    <div class="form-row">
      <div class="fld">
        <label>Target Amount (KES)</label>
        <input type="number" name="target" value="{{ $target }}" min="0" step="1000" required>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Update Target</button>
  </form>
</div>
@endsection
