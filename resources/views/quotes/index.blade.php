@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Quote Builder</div>
    <div class="page-subtitle">{{ $quotes->count() }} quote(s)</div>
  </div>
  <a href="{{ route('bms.quotes.create') }}" class="btn btn-primary">+ New Quote</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Client</th>
          <th>Branch</th>
          <th>Total</th>
          <th>Status</th>
          <th>Prepared By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($quotes as $quote)
          @php
            $items = $quote->items ?? [];
            $subtotal = collect($items)->sum(fn($i) => ($i['qty'] ?? 1) * ($i['unit_price'] ?? 0));
            $total = $subtotal * (1 + (($quote->vat_rate ?? 16) / 100));
          @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">QT-{{ str_pad($quote->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($quote->date)->format('d M Y') }}</td>
            <td style="font-weight:600;">{{ $quote->client_name }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $quote->branch }}</span></td>
            <td><span class="mono">KSh {{ number_format($total, 2) }}</span></td>
            <td>
              @php $statusColors = ['draft'=>'badge-gray','sent'=>'badge-blue','approved'=>'badge-green','declined'=>'badge-red']; @endphp
              <span class="badge {{ $statusColors[$quote->status ?? 'draft'] ?? 'badge-gray' }}">{{ ucfirst($quote->status ?? 'draft') }}</span>
            </td>
            <td style="font-size:12px;color:var(--text2);">{{ $quote->prepared_by ?? '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.quotes.edit', $quote->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <a href="{{ route('bms.quotes.pdf', $quote->id) }}" class="btn btn-secondary btn-sm" title="View PDF" target="_blank">PDF</a>
                <form method="POST" action="{{ route('bms.quotes.destroy', $quote->id) }}" onsubmit="return confirm('Delete quote?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="empty-state"><div class="empty-icon">📝</div><p>No quotes yet</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
