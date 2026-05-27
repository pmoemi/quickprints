@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Payslips</div>
    <div class="page-subtitle">{{ $payroll->count() }} entries for {{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</div>
  </div>
</div>

<form method="GET" class="filter-bar">
  <input type="month" name="month" value="{{ $month }}" class="search-input" onchange="this.form.submit()">
  <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
</form>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Staff</th><th>Month</th><th>Gross</th><th>NHIF</th><th>NSSF</th><th>PAYE</th><th>Net Pay</th><th>Status</th></tr>
      </thead>
      <tbody>
        @forelse($payroll as $entry)
          @php $member = $staff[$entry->staff_id] ?? null; @endphp
          <tr>
            <td style="font-weight:600;">{{ $entry->staff_name ?? $member?->name ?? '—' }}</td>
            <td class="mono" style="font-size:12px;">{{ $entry->month }}</td>
            <td class="mono">{{ $bmsCurrency }} {{ number_format($entry->gross_salary ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->nhif ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->nssf ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->paye ?? 0) }}</td>
            <td><span class="mono text-green" style="font-weight:700;">{{ $bmsCurrency }} {{ number_format($entry->net_pay ?? 0) }}</span></td>
            <td>
              @if(($entry->status ?? 'pending') === 'paid')
                <span class="badge badge-green">Paid</span>
              @else
                <span class="badge badge-orange">Pending</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📄</div><p>No payslips for this month</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
