@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Notifications</div>
    <div class="page-subtitle">{{ $items->whereNull('read_at')->count() }} unread</div>
  </div>
  @if($items->whereNull('read_at')->count() > 0)
    <form method="POST" action="{{ route('bms.notifications.read-all') }}">
      @csrf
      <button type="submit" class="btn btn-secondary btn-sm">Mark All Read</button>
    </form>
  @endif
</div>

<div class="card">
  @forelse($items as $notif)
    <div class="activity-item" style="{{ !$notif->read_at ? 'background:var(--accent-dim);border-radius:var(--radius);padding:10px 12px;margin-bottom:4px;' : '' }}">
      <div class="activity-icon" style="background:{{ $notif->read_at ? 'var(--bg4)' : 'var(--accent-dim)' }};color:{{ $notif->read_at ? 'var(--text3)' : 'var(--accent)' }}">🔔</div>
      <div class="activity-text">
        {{ $notif->message }}
        @if($notif->job_id)
          — <a href="{{ route('bms.jobs.show', $notif->job_id) }}" class="text-accent">{{ $notif->job_id }}</a>
        @endif
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <span class="activity-time">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</span>
        @if(!$notif->read_at)
          <form method="POST" action="{{ route('bms.notifications.read', $notif->id) }}">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">Mark Read</button>
          </form>
        @else
          <span class="badge badge-gray">Read</span>
        @endif
      </div>
    </div>
  @empty
    <div class="empty-state"><div class="empty-icon">🔔</div><p>No notifications</p></div>
  @endforelse
</div>
@endsection
