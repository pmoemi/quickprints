@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Audit Log</div>
    <div class="page-subtitle">Last {{ $logs->count() }} system events</div>
  </div>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>When</th><th>User</th><th>Branch</th><th>IP</th><th>Action</th></tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="font-size:12px;color:var(--text3);white-space:nowrap;">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
            <td style="font-size:13px;font-weight:500;">{{ $log->user ?? 'System' }}</td>
            <td style="font-size:12px;color:var(--text3);">{{ $log->branch ?? '—' }}</td>
            <td class="mono" style="font-size:11px;color:var(--text3);">{{ $log->ip ?? '—' }}</td>
            <td style="font-size:13px;color:var(--text2);">{{ $log->action }}</td>
          </tr>
        @empty
          <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🔍</div><p>No audit events recorded</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
