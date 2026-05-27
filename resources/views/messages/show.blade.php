@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ $msg->subject }}</div>
    <div class="page-subtitle">From {{ $msg->from_name ?? 'System' }}</div>
  </div>
  <a href="{{ route('bms.messages.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:720px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border);">
    <div>
      <div style="font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">From</div>
      <div style="font-size:14px;font-weight:600;">{{ $msg->from_name ?? 'System' }}</div>
    </div>
    <div style="font-size:12px;color:var(--text3);">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y H:i') }}</div>
  </div>
  <div style="font-size:14px;color:var(--text2);line-height:1.7;white-space:pre-wrap;">{{ $msg->body }}</div>
</div>
@endsection
