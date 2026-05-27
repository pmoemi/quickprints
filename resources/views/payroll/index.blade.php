@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Payroll</div>
    <div class="page-subtitle">{{ $entries->count() }} entries</div>
  </div>
  <a href="{{ route('bms.payroll.create') }}" class="btn btn-primary">+ Add Payroll Entry</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Month</th><th>Staff</th><th>Gross Salary</th><th>NHIF</th><th>NSSF</th><th>PAYE</th><th>Net Pay</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($entries as $entry)
          <tr>
            <td class="mono" style="font-size:12px;">{{ $entry->month }}</td>
            <td style="font-weight:600;">{{ $entry->staff_name ?? '—' }}</td>
            <td class="mono">KSh {{ number_format($entry->gross_salary ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->nhif ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->nssf ?? 0) }}</td>
            <td class="mono" style="font-size:12px;">{{ number_format($entry->paye ?? 0) }}</td>
            <td><span class="mono text-green" style="font-weight:700;">KSh {{ number_format($entry->net_pay ?? 0) }}</span></td>
            <td>
              @if(($entry->status ?? 'pending') === 'paid')
                <span class="badge badge-green">Paid</span>
              @else
                <span class="badge badge-orange">Pending</span>
              @endif
            </td>
            <td>
              <form method="POST" action="{{ route('bms.payroll.destroy', $entry->id) }}" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">💵</div><p>No payroll entries</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
