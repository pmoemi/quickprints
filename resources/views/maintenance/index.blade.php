@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">System Maintenance</div>
    <div class="page-subtitle">Admin tools and diagnostics</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
  <div class="card">
    <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Cache Management</div>
    <p style="font-size:13px;color:var(--text3);margin-bottom:16px;">Run <code style="background:var(--bg3);padding:2px 6px;border-radius:4px;">php artisan cache:clear</code> and <code style="background:var(--bg3);padding:2px 6px;border-radius:4px;">php artisan view:clear</code> from the terminal to clear caches.</p>
    <div class="mono" style="background:var(--bg3);border-radius:6px;padding:12px;font-size:12px;color:var(--text2);">php artisan cache:clear && php artisan view:clear</div>
  </div>

  <div class="card">
    <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Storage Link</div>
    <p style="font-size:13px;color:var(--text3);margin-bottom:16px;">Run from terminal to link storage for uploaded files:</p>
    <div class="mono" style="background:var(--bg3);border-radius:6px;padding:12px;font-size:12px;color:var(--text2);">php artisan storage:link</div>
  </div>

  <div class="card">
    <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Environment</div>
    <div style="font-size:12px;color:var(--text3);">
      <div style="margin-bottom:6px;display:flex;justify-content:space-between;">
        <span>Laravel</span><span style="color:var(--text2);">{{ app()->version() }}</span>
      </div>
      <div style="margin-bottom:6px;display:flex;justify-content:space-between;">
        <span>PHP</span><span style="color:var(--text2);">{{ PHP_VERSION }}</span>
      </div>
      <div style="margin-bottom:6px;display:flex;justify-content:space-between;">
        <span>Environment</span><span style="color:var(--accent);">{{ app()->environment() }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;">
        <span>Debug Mode</span><span style="color:{{ config('app.debug') ? 'var(--yellow)' : 'var(--green)' }};">{{ config('app.debug') ? 'ON' : 'OFF' }}</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Audit Log</div>
    <p style="font-size:13px;color:var(--text3);margin-bottom:16px;">Review all system events and user actions.</p>
    <a href="{{ route('bms.audit.index') }}" class="btn btn-secondary">View Audit Log</a>
  </div>
</div>
@endsection
