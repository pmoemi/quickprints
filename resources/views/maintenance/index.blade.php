@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">System Maintenance</div>
    <div class="page-subtitle">Admin tools, diagnostics, and data reset</div>
  </div>
</div>

@if($canResetData)
<div class="card mb-2">
  <div class="card-header">
    <div>
      <div class="card-title">Services Catalogue</div>
      <div style="font-size:11px;color:var(--text3);margin-top:3px;">Signages · Branding · Printing — safe to run anytime</div>
    </div>
    <span class="badge badge-blue">{{ number_format($serviceCount) }} in catalogue</span>
  </div>
  <p style="font-size:12px;color:var(--text3);margin-bottom:14px;line-height:1.5;">
    Adds {{ number_format($catalogTotal) }} default services (Signages, Branding, Printing) when missing. Does not delete or change existing services, jobs, clients, or any other data.
  </p>
  <form method="POST" action="{{ route('bms.maintenance.seed-services') }}">
    @csrf
    <button type="submit" class="btn btn-primary btn-sm">Seed Services Catalogue</button>
  </form>
  <div style="margin-top:12px;font-size:11px;color:var(--text3);font-family:var(--mono);">
    php artisan bms:seed-services --force
  </div>
</div>

<div class="card mb-2" style="border-color:var(--border2);">
  <div class="card-header">
    <div>
      <div class="card-title">Data Reset</div>
      <div style="font-size:11px;color:var(--text3);margin-top:3px;">Manage operational records and demo sample data</div>
    </div>
    <span class="badge badge-orange">{{ number_format($totalRecords) }} records</span>
  </div>

  <div class="alert alert-warn" style="margin-bottom:16px;">
    Destructive actions cannot be undone. Settings, branding, the services catalogue, and user accounts are always preserved when clearing.
  </div>

  @if($totalRecords > 0)
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:18px;">
      @foreach(collect($recordCounts)->filter(fn($c) => $c > 0)->take(12) as $table => $count)
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:10px 12px;">
          <div style="font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;">{{ str_replace('_', ' ', $table) }}</div>
          <div style="font-size:18px;font-weight:700;font-family:var(--mono);color:var(--text);margin-top:4px;">{{ number_format($count) }}</div>
        </div>
      @endforeach
    </div>
  @else
    <p style="font-size:13px;color:var(--text3);margin-bottom:18px;">No operational records in the database. Load demo data to get started.</p>
  @endif

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    {{-- Clear all records --}}
    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:16px;">
      <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Clear All Records</div>
      <p style="font-size:12px;color:var(--text3);margin-bottom:14px;line-height:1.5;">
        Delete jobs, clients, sales, inventory, finance, messages, and all other operational data. Keeps settings and users.
      </p>
      <form method="POST" action="{{ route('bms.maintenance.clear-data') }}" onsubmit="return confirmReset(this, 'clear all operational records')">
        @csrf
        <div class="fld" style="margin-bottom:10px;">
          <label style="font-size:10px;">Type RESET to confirm</label>
          <input type="text" name="confirmation" autocomplete="off" placeholder="RESET" required style="text-transform:uppercase;">
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:11px;color:var(--text2);margin-bottom:12px;cursor:pointer;">
          <input type="checkbox" name="keep_audit" value="1"> Keep audit log history
        </label>
        <button type="submit" class="btn btn-danger btn-sm">Clear Records</button>
      </form>
    </div>

    {{-- Load demo data --}}
    <div style="background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:16px;">
      <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Load Demo Data</div>
      <p style="font-size:12px;color:var(--text3);margin-bottom:14px;line-height:1.5;">
        Insert or refresh sample jobs, clients, sales, staff, and finance records. Also resets settings to defaults and upserts demo users.
      </p>
      <div style="font-size:11px;color:var(--text3);margin-bottom:12px;font-family:var(--mono);">
        {{ $demoLogin }} / {{ $demoPassword }}
      </div>
      <form method="POST" action="{{ route('bms.maintenance.seed-demo') }}" onsubmit="return confirm('Load demo sample data? Existing demo IDs will be updated.')">
        @csrf
        <button type="submit" class="btn btn-secondary btn-sm">Load Demo Data</button>
      </form>
    </div>

    {{-- Full reset to demo --}}
    <div style="background:var(--red-dim);border:1px solid rgba(220,38,38,.25);border-radius:10px;padding:16px;">
      <div style="font-size:14px;font-weight:700;margin-bottom:6px;color:var(--red);">Reset to Demo</div>
      <p style="font-size:12px;color:var(--text2);margin-bottom:14px;line-height:1.5;">
        Wipe all operational records, then load a fresh demo dataset. Best for demos, training, or starting over quickly.
      </p>
      <form method="POST" action="{{ route('bms.maintenance.reset-demo') }}" onsubmit="return confirmReset(this, 'wipe all data and reload demo')">
        @csrf
        <div class="fld" style="margin-bottom:10px;">
          <label style="font-size:10px;">Type RESET to confirm</label>
          <input type="text" name="confirmation" autocomplete="off" placeholder="RESET" required style="text-transform:uppercase;">
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:11px;color:var(--text2);margin-bottom:12px;cursor:pointer;">
          <input type="checkbox" name="keep_audit" value="1"> Keep audit log history
        </label>
        <button type="submit" class="btn btn-danger btn-sm">Reset to Demo</button>
      </form>
    </div>
  </div>

  <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);">
    <div style="font-size:11px;color:var(--text3);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Terminal commands</div>
    <div class="mono" style="background:var(--bg3);border-radius:6px;padding:12px;font-size:11px;color:var(--text2);line-height:1.8;">
      php artisan bms:reset-data counts<br>
      php artisan bms:reset-data clear --force<br>
      php artisan bms:reset-data seed --force<br>
      php artisan bms:reset-data demo --force
    </div>
  </div>
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
  <div class="card">
    <div style="font-size:15px;font-weight:700;margin-bottom:6px;">Cache Management</div>
    <p style="font-size:13px;color:var(--text3);margin-bottom:16px;">Run from the terminal to clear caches:</p>
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

@if(!$canResetData)
  <div class="alert alert-info" style="margin-top:16px;">Data reset tools require <strong>settings → delete</strong> permission (Admin or equivalent role).</div>
@endif
@endsection

@push('scripts')
<script>
function confirmReset(form, action) {
  const input = form.querySelector('input[name=confirmation]');
  if ((input?.value || '').trim().toUpperCase() !== 'RESET') {
    alert('Type RESET to confirm.');
    return false;
  }
  return confirm('Are you sure you want to ' + action + '? This cannot be undone.');
}
</script>
@endpush
