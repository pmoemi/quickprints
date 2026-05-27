@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

@php $es = $settings['email_settings'] ?? []; @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

  {{-- SMTP Configuration --}}
  <div class="card">
    <div class="card-header"><div class="card-title">SMTP Configuration</div></div>
    <form method="POST" action="{{ route('bms.settings.email.update') }}">
      @csrf @method('PUT')

      <div class="form-row cols-2">
        <div class="fld">
          <label>SMTP Host</label>
          <input type="text" name="host" value="{{ old('host', $es['host'] ?? '') }}"
                 placeholder="smtp.gmail.com">
        </div>
        <div class="fld">
          <label>Port</label>
          <input type="number" name="port" value="{{ old('port', $es['port'] ?? 587) }}"
                 placeholder="587" style="width:100%;">
        </div>
      </div>

      <div class="form-row cols-2">
        <div class="fld">
          <label>Encryption</label>
          <select name="encryption">
            @foreach(['tls' => 'TLS (Recommended)', 'ssl' => 'SSL', 'starttls' => 'STARTTLS', '' => 'None'] as $val => $label)
              <option value="{{ $val }}" {{ old('encryption', $es['encryption'] ?? 'tls') === $val ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="fld">
          <label>Username / Email</label>
          <input type="text" name="username" value="{{ old('username', $es['username'] ?? '') }}"
                 placeholder="you@gmail.com" autocomplete="off">
        </div>
      </div>

      <div class="fld">
        <label>Password</label>
        <input type="password" name="password" placeholder="Leave blank to keep current"
               autocomplete="new-password">
        @if(!empty($es['password']))
          <small style="color:var(--text3);font-size:11px;">Password is set — leave blank to keep unchanged.</small>
        @endif
      </div>

      <div class="form-row cols-2">
        <div class="fld">
          <label>From Name</label>
          <input type="text" name="from_name" value="{{ old('from_name', $es['from_name'] ?? '') }}"
                 placeholder="{{ $settings['company_name'] ?? 'Quick Prints' }}">
        </div>
        <div class="fld">
          <label>From Email Address</label>
          <input type="email" name="from_address" value="{{ old('from_address', $es['from_address'] ?? '') }}"
                 placeholder="noreply@yourcompany.com">
        </div>
      </div>

      <hr style="border-color:var(--border);margin:20px 0;">

      <div class="card-title" style="margin-bottom:14px;">Email Notifications</div>
      <p style="font-size:12px;color:var(--text3);margin-bottom:12px;">
        When enabled, emails are sent automatically to clients with a valid email address on file.
      </p>

      @php $notifs = $es['notifications'] ?? []; @endphp
      @foreach([
        'job_created' => ['Job Created', 'Email client when a new job is created for them'],
        'job_updated' => ['Job Stage Changed', 'Email client when their job advances to a new stage'],
        'quote_sent'  => ['Quote Sent',  'Email client when a quotation is created for them'],
      ] as $key => [$label, $desc])
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
        <label class="toggle" style="flex-shrink:0;">
          <input type="checkbox" name="notif_{{ $key }}" value="1"
                 {{ ($notifs[$key] ?? false) ? 'checked' : '' }}>
          <span class="toggle-slider"></span>
        </label>
        <div>
          <div style="font-size:13px;font-weight:600;">{{ $label }}</div>
          <div style="font-size:12px;color:var(--text3);">{{ $desc }}</div>
        </div>
      </div>
      @endforeach

      <div style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">Save Email Settings</button>
      </div>
    </form>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Connection Status --}}
    <div class="card">
      <div class="card-header"><div class="card-title">Connection Status</div></div>
      @if(!empty($es['host']) && !empty($es['username']))
        <div style="display:flex;align-items:center;gap:10px;padding:12px 0;">
          <span style="width:10px;height:10px;border-radius:50%;background:var(--green);display:inline-block;flex-shrink:0;"></span>
          <div>
            <div style="font-size:13px;font-weight:600;">SMTP Configured</div>
            <div style="font-size:12px;color:var(--text3);">{{ $es['username'] }} → {{ $es['host'] }}:{{ $es['port'] ?? 587 }}</div>
          </div>
        </div>
      @else
        <div style="display:flex;align-items:center;gap:10px;padding:12px 0;">
          <span style="width:10px;height:10px;border-radius:50%;background:var(--text3);display:inline-block;flex-shrink:0;"></span>
          <div style="font-size:13px;color:var(--text3);">Not configured — emails will not be sent.</div>
        </div>
      @endif
    </div>

    {{-- Send Test Email --}}
    <div class="card">
      <div class="card-header"><div class="card-title">Test Configuration</div></div>
      @if(session('test_ok'))
        <div class="alert alert-success" style="margin-bottom:12px;">{{ session('test_ok') }}</div>
      @endif
      @if(session('test_error'))
        <div class="alert alert-error" style="margin-bottom:12px;">{{ session('test_error') }}</div>
      @endif
      <form method="POST" action="{{ route('bms.settings.email.test') }}">
        @csrf
        <div class="fld">
          <label>Send test email to</label>
          <input type="email" name="test_to" value="{{ old('test_to', auth()->user()->email) }}"
                 placeholder="recipient@example.com" required>
        </div>
        <button type="submit" class="btn btn-secondary" style="margin-top:8px;">
          Send Test Email
        </button>
        <p style="font-size:11px;color:var(--text3);margin-top:8px;">
          Sends a test message using the current SMTP configuration above.
        </p>
      </form>
    </div>

  </div>
</div>
@endsection
