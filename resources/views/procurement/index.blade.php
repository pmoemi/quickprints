@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Procurement</div>
    <div class="page-subtitle">{{ $entries->count() }} entries</div>
  </div>
  <a href="{{ route('bms.procurement.create') }}" class="btn btn-primary">+ Add Entry</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Date</th><th>Item</th><th>Supplier</th><th>Qty</th><th>Unit Cost</th><th>Total</th><th>Branch</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($entries as $entry)
          <tr>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
            <td style="font-weight:600;">{{ $entry->item_name ?? $entry->description ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $entry->supplier ?? '—' }}</td>
            <td class="mono">{{ $entry->qty ?? '—' }}</td>
            <td class="mono">{{ $bmsCurrency }} {{ number_format($entry->unit_cost ?? 0) }}</td>
            <td class="mono text-accent">{{ $bmsCurrency }} {{ number_format($entry->total ?? (($entry->qty ?? 1) * ($entry->unit_cost ?? 0))) }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $entry->branch }}</td>
            <td>
              <span class="badge {{ ($entry->status ?? 'pending') === 'received' ? 'badge-green' : 'badge-orange' }}">
                {{ ucfirst($entry->status ?? 'pending') }}
              </span>
            </td>
            <td>
              <form method="POST" action="{{ route('bms.procurement.destroy', $entry->id) }}" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📥</div><p>No procurement entries</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
