@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Messages</div>
    <div class="page-subtitle">{{ $inbox->count() }} in inbox</div>
  </div>
  <a href="{{ route('bms.messages.create') }}" class="btn btn-primary">+ Compose</a>
</div>

<div class="tabs">
  <a href="#" class="tab-item active" onclick="showTab('inbox',this);return false;">Inbox ({{ $inbox->whereNull('read_at')->count() }})</a>
  <a href="#" class="tab-item" onclick="showTab('sent',this);return false;">Sent ({{ $sent->count() }})</a>
</div>

<div id="tab-inbox" class="msg-layout" style="height:auto;">
  <div class="msg-list" style="border:1px solid var(--border);border-radius:var(--radius);">
    @forelse($inbox as $msg)
      <a href="{{ route('bms.messages.show', $msg->id) }}" class="msg-list-item {{ !$msg->read_at ? 'unread' : '' }}" style="text-decoration:none;">
        <div class="msg-from">{{ $msg->from_name ?? 'System' }}</div>
        <div class="msg-subject">{{ $msg->subject }}</div>
        <div class="msg-preview">{{ Str::limit($msg->body, 60) }}</div>
        <div style="font-size:10px;color:var(--text3);margin-top:4px;">{{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}</div>
      </a>
    @empty
      <div class="empty-state"><div class="empty-icon">✉️</div><p>No messages</p></div>
    @endforelse
  </div>
</div>

<div id="tab-sent" style="display:none;">
  <div class="card">
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Subject</th><th>Sent</th></tr></thead>
        <tbody>
          @forelse($sent as $msg)
            <tr>
              <td>{{ $msg->subject }}</td>
              <td style="font-size:12px;color:var(--text2);">{{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}</td>
            </tr>
          @empty
            <tr><td colspan="2"><div class="empty-state"><p>No sent messages</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
function showTab(id, el) {
  ['inbox','sent'].forEach(t => document.getElementById('tab-'+t).style.display = t===id ? '' : 'none');
  document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
</script>
@endpush
@endsection
