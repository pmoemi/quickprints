@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Leave Requests</div>
    <div class="page-subtitle">{{ $items->count() }} request(s)</div>
  </div>
  <a href="{{ route('bms.leave.create') }}" class="btn btn-primary">+ New Request</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Staff</th><th>Leave Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td style="font-weight:600;">{{ $item->staff_name ?? '—' }}</td>
            <td><span class="badge badge-blue">{{ $item->leave_type ?? 'Annual' }}</span></td>
            <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($item->start_date ?? $item->requested_date)->format('d M Y') }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '—' }}</td>
            <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item->reason ?? '—' }}</td>
            <td>
              @if($item->status === 'approved')
                <span class="badge badge-green">Approved</span>
              @elseif($item->status === 'rejected')
                <span class="badge badge-red">Rejected</span>
              @else
                <span class="badge badge-orange">Pending</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                @if($item->status === 'pending')
                  <form method="POST" action="{{ route('bms.leave.approve', $item->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                  </form>
                  <form method="POST" action="{{ route('bms.leave.reject', $item->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🌴</div><p>No leave requests</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
