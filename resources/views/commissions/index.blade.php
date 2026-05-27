@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Commissions</div>
    <div class="page-subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }} · {{ $rate }}% commission rate</div>
  </div>
  <form method="GET" action="{{ route('bms.commissions.index') }}" style="display:flex;gap:8px;align-items:center;">
    <input type="month" name="month" class="filter-select" value="{{ $month }}">
    <button type="submit" class="btn btn-secondary">Go</button>
  </form>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Sales Rep</th><th>Deals</th><th>Total Sales</th><th>Commission ({{ $rate }}%)</th></tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          <tr>
            <td style="font-weight:600;">{{ $row['rep'] }}</td>
            <td style="font-size:12px;color:var(--text3);">{{ $row['deals'] }}</td>
            <td style="font-weight:600;">KES {{ number_format($row['sales']) }}</td>
            <td style="font-weight:700;color:var(--green);">KES {{ number_format($row['commission']) }}</td>
          </tr>
        @empty
          <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">💼</div><p>No commission data for this period</p></div></td></tr>
        @endforelse
      </tbody>
      @if(count($rows) > 1)
        <tfoot>
          <tr style="border-top:2px solid var(--border);">
            <td style="font-weight:700;">Total</td>
            <td>{{ array_sum(array_column($rows, 'deals')) }}</td>
            <td style="font-weight:700;">KES {{ number_format(array_sum(array_column($rows, 'sales'))) }}</td>
            <td style="font-weight:700;color:var(--green);">KES {{ number_format(array_sum(array_column($rows, 'commission'))) }}</td>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
