@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Recurring Bills</div>
    <div class="page-subtitle">{{ $items->count() }} recurring bill(s)</div>
  </div>
  <a href="{{ route('bms.recurring-bills.create') }}" class="btn btn-primary">+ Add Bill</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Category</th><th>Vendor</th><th>Branch</th><th>Amount</th><th>Due Day</th><th>Last Paid</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          @php
            $dueDay = (int)$item->due_day;
            $today = now()->day;
            $isOverdue = $today > $dueDay && (!$item->last_paid_at || \Carbon\Carbon::parse($item->last_paid_at)->month < now()->month);
          @endphp
          <tr>
            <td style="font-weight:600;">{{ $item->name }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->category ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->vendor ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->branch ?? '—' }}</td>
            <td style="font-weight:600;">KES {{ number_format($item->amount) }}</td>
            <td style="font-size:12px;">
              <span style="color:{{ $isOverdue ? 'var(--red)' : 'var(--text)' }};">{{ $dueDay }}{{ match(true) { $dueDay % 10 === 1 && $dueDay !== 11 => 'st', $dueDay % 10 === 2 && $dueDay !== 12 => 'nd', $dueDay % 10 === 3 && $dueDay !== 13 => 'rd', default => 'th' } }}</span>
              @if($isOverdue) <span class="badge badge-red" style="margin-left:4px;">Overdue</span> @endif
            </td>
            <td style="font-size:12px;color:var(--text3);">{{ $item->last_paid_at ? \Carbon\Carbon::parse($item->last_paid_at)->format('d M Y') : '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <form method="POST" action="{{ route('bms.recurring-bills.update', $item->id) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="mark_paid" value="1">
                  <button type="submit" class="btn btn-success btn-sm">Paid</button>
                </form>
                <form method="POST" action="{{ route('bms.recurring-bills.destroy', $item->id) }}" onsubmit="return confirm('Delete this bill?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Del</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📅</div><p>No recurring bills set up</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
