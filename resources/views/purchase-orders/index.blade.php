@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Purchase Orders</div>
    <div class="page-subtitle">{{ $items->count() }} purchase order(s)</div>
  </div>
  <a href="{{ route('bms.purchase-orders.create') }}" class="btn btn-primary">+ New PO</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Date</th><th>Supplier</th><th>Description</th><th>Branch</th><th>Amount</th><th>Status</th><th>Requested By</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $item->id }}</td>
            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
            <td style="font-weight:600;">{{ $item->supplier }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->description ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->branch ?? '—' }}</td>
            <td style="font-weight:600;">KES {{ number_format($item->amount) }}</td>
            <td>
              @php $s = $item->status ?? 'pending'; @endphp
              <span class="badge {{ $s === 'approved' ? 'badge-green' : ($s === 'rejected' ? 'badge-red' : 'badge-gray') }}">{{ ucfirst($s) }}</span>
            </td>
            <td style="font-size:12px;color:var(--text3);">{{ $item->requested_by ?? '—' }}</td>
            <td>
              <form method="POST" action="{{ route('bms.purchase-orders.destroy', $item->id) }}" onsubmit="return confirm('Delete this PO?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📋</div><p>No purchase orders</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
