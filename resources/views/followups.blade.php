@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Follow-Ups</div>
    <div class="page-subtitle">{{ $leads->count() }} lead(s) to follow up</div>
  </div>
  <a href="{{ route('bms.leads.create') }}" class="btn btn-primary">+ New Lead</a>
</div>

@php
  $due = $leads->filter(fn($l) => $l->follow_up_date && $l->follow_up_date->isPast());
  $today = $leads->filter(fn($l) => $l->follow_up_date && $l->follow_up_date->isToday());
  $upcoming = $leads->filter(fn($l) => $l->follow_up_date && $l->follow_up_date->isFuture());
@endphp

@if($due->count())
<div style="margin-bottom:24px;">
  <div class="card-title" style="color:var(--red);margin-bottom:10px;">Overdue ({{ $due->count() }})</div>
  <div class="card" style="padding:0;">
    @foreach($due as $lead)
      <div class="activity-item" style="background:rgba(239,68,68,.06);border-radius:0;">
        <div class="activity-icon" style="background:rgba(239,68,68,.15);color:var(--red);">!</div>
        <div class="activity-text">
          <strong>{{ $lead->company ?? $lead->name }}</strong>
          @if($lead->name && $lead->company) <span style="color:var(--text3);">· {{ $lead->name }}</span> @endif
          <span class="badge stage-{{ strtolower($lead->status ?? 'new') }}" style="margin-left:6px;">{{ $lead->status ?? 'new' }}</span>
          @if($lead->value) <span style="color:var(--text3);font-size:12px;margin-left:6px;">KES {{ number_format($lead->value) }}</span> @endif
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="font-size:12px;color:var(--red);">{{ $lead->follow_up_date->format('d M') }}</span>
          <a href="{{ route('bms.leads.edit', $lead->id) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

@if($today->count())
<div style="margin-bottom:24px;">
  <div class="card-title" style="color:var(--yellow);margin-bottom:10px;">Today ({{ $today->count() }})</div>
  <div class="card" style="padding:0;">
    @foreach($today as $lead)
      <div class="activity-item" style="background:rgba(250,204,21,.06);border-radius:0;">
        <div class="activity-icon" style="background:rgba(250,204,21,.15);color:var(--yellow);">⏰</div>
        <div class="activity-text">
          <strong>{{ $lead->company ?? $lead->name }}</strong>
          @if($lead->name && $lead->company) <span style="color:var(--text3);">· {{ $lead->name }}</span> @endif
          <span class="badge stage-{{ strtolower($lead->status ?? 'new') }}" style="margin-left:6px;">{{ $lead->status ?? 'new' }}</span>
          @if($lead->value) <span style="color:var(--text3);font-size:12px;margin-left:6px;">KES {{ number_format($lead->value) }}</span> @endif
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="font-size:12px;color:var(--yellow);">Today</span>
          <a href="{{ route('bms.leads.edit', $lead->id) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

@if($upcoming->count())
<div style="margin-bottom:24px;">
  <div class="card-title" style="margin-bottom:10px;">Upcoming ({{ $upcoming->count() }})</div>
  <div class="card" style="padding:0;">
    @foreach($upcoming as $lead)
      <div class="activity-item">
        <div class="activity-icon">📋</div>
        <div class="activity-text">
          <strong>{{ $lead->company ?? $lead->name }}</strong>
          @if($lead->name && $lead->company) <span style="color:var(--text3);">· {{ $lead->name }}</span> @endif
          <span class="badge stage-{{ strtolower($lead->status ?? 'new') }}" style="margin-left:6px;">{{ $lead->status ?? 'new' }}</span>
          @if($lead->value) <span style="color:var(--text3);font-size:12px;margin-left:6px;">KES {{ number_format($lead->value) }}</span> @endif
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="activity-time">{{ $lead->follow_up_date->format('d M Y') }}</span>
          <a href="{{ route('bms.leads.edit', $lead->id) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

@if(!$due->count() && !$today->count() && !$upcoming->count())
<div class="card">
  <div class="empty-state"><div class="empty-icon">✅</div><p>No follow-ups scheduled</p></div>
</div>
@endif
@endsection
