@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">VAT Report</div>
    <div class="page-subtitle">Output VAT for {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
  </div>
  <form method="GET" action="{{ route('bms.vat-report') }}" style="display:flex;gap:8px;align-items:center;">
    <input type="month" name="month" class="filter-select" value="{{ $month }}">
    <button type="submit" class="btn btn-secondary">Go</button>
  </form>
</div>

<div class="stats-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Gross Revenue</div>
    <div class="stat-value">KES {{ number_format($taxable) }}</div>
    <div class="stat-sub">VAT inclusive</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Net Revenue</div>
    <div class="stat-value">KES {{ number_format($net) }}</div>
    <div class="stat-sub">Before VAT</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Output VAT ({{ $vatRate }}%)</div>
    <div class="stat-value" style="color:var(--yellow);">KES {{ number_format($vat) }}</div>
    <div class="stat-sub">Payable to KRA</div>
  </div>
</div>

<div class="card" style="max-width:600px;">
  <div class="card-title" style="margin-bottom:16px;">Breakdown</div>
  <table style="width:100%;font-size:13px;">
    <tbody>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 0;color:var(--text3);">Sales Log Revenue</td>
        <td style="text-align:right;font-weight:600;">KES {{ number_format($sales) }}</td>
      </tr>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 0;color:var(--text3);">Paid Print Jobs</td>
        <td style="text-align:right;font-weight:600;">KES {{ number_format($jobs) }}</td>
      </tr>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 0;color:var(--text3);">Total Gross</td>
        <td style="text-align:right;font-weight:600;">KES {{ number_format($taxable) }}</td>
      </tr>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 0;color:var(--text3);">VAT Rate</td>
        <td style="text-align:right;">{{ $vatRate }}%</td>
      </tr>
      <tr style="border-bottom:1px solid var(--border);">
        <td style="padding:10px 0;color:var(--text3);">Net (ex-VAT)</td>
        <td style="text-align:right;font-weight:600;">KES {{ number_format($net) }}</td>
      </tr>
      <tr>
        <td style="padding:10px 0;font-weight:700;color:var(--yellow);">VAT Payable</td>
        <td style="text-align:right;font-weight:700;color:var(--yellow);">KES {{ number_format($vat) }}</td>
      </tr>
    </tbody>
  </table>
</div>
@endsection
