@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Expenses (Opex)</div>
    <div class="page-subtitle">{{ $items->count() }} entries · Total: {{ $bmsCurrency }} {{ number_format($items->sum('amount')) }}</div>
  </div>
  <a href="{{ route('bms.opex.create') }}" class="btn btn-primary">+ Add Expense</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Date</th><th>Description</th><th>Amount</th><th>Branch</th><th>Method</th><th>Paid By</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
            <td>{{ $item->description }}</td>
            <td><span class="mono">{{ $bmsCurrency }} {{ number_format($item->amount) }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $item->branch }}</span></td>
            <td><span class="badge badge-gray">{{ $item->pay_method ?? '—' }}</span></td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->paid_by ?? '—' }}</td>
            <td>
              <form method="POST" action="{{ route('bms.opex.destroy', $item->id) }}" onsubmit="return confirm('Delete expense?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💳</div><p>No expenses recorded</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
