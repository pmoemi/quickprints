@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Petty Cash</div>
    <div class="page-subtitle">{{ $entries->count() }} entries · Total: {{ $bmsCurrency }} {{ number_format($entries->sum('amount')) }}</div>
  </div>
  <a href="{{ route('bms.pettycash.create') }}" class="btn btn-primary">+ Add Entry</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Date</th><th>Description</th><th>Amount</th><th>Branch</th><th>Submitted By</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($entries as $entry)
          <tr>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
            <td>{{ $entry->description }}</td>
            <td class="mono">{{ $bmsCurrency }} {{ number_format($entry->amount) }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $entry->branch }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $entry->submitted_by ?? '—' }}</td>
            <td>
              @if($entry->status === 'approved')
                <span class="badge badge-green">Approved</span>
              @elseif($entry->status === 'rejected')
                <span class="badge badge-red">Rejected</span>
              @else
                <span class="badge badge-orange">Pending</span>
              @endif
            </td>
            <td>
              <form method="POST" action="{{ route('bms.pettycash.destroy', $entry->id) }}" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💊</div><p>No petty cash entries</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
