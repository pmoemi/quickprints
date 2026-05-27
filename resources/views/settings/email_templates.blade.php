@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

@php
  $tpls = $settings['email_templates'] ?? [];
  $tabs = [
    'job_created'  => ['label' => 'Job Created',       'icon' => '📋'],
    'job_status'   => ['label' => 'Job Status Update', 'icon' => '🔄'],
    'job_invoice'  => ['label' => 'Invoice Email',     'icon' => '🧾'],
    'quote_sent'   => ['label' => 'Quote Sent',        'icon' => '📝'],
  ];
  $activeTab = request('tpl', 'job_created');
  if (!array_key_exists($activeTab, $tabs)) $activeTab = 'job_created';
  $t = $tpls[$activeTab] ?? [];
@endphp

@if(session('success'))
  <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
@endif

{{-- Template type tab bar --}}
<div style="display:flex;gap:2px;margin-bottom:20px;border-bottom:2px solid var(--border);flex-wrap:wrap;">
  @foreach($tabs as $key => $tab)
    <a href="{{ route('bms.settings.email-templates') }}?tpl={{ $key }}"
       style="padding:9px 18px;border-radius:6px 6px 0 0;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:-2px;display:inline-flex;align-items:center;gap:6px;
              {{ $activeTab === $key ? 'background:var(--accent);color:#fff;' : 'color:var(--text2);' }}">
      {{ $tab['label'] }}
    </a>
  @endforeach
</div>

{{-- Single form — save button posts to update, reset button uses formaction to reset route --}}
<form method="POST" action="{{ route('bms.settings.email-templates.update') }}">
  @csrf @method('PUT')
  <input type="hidden" name="tpl_type" value="{{ $activeTab }}">

  <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start;">

    {{-- Editor panel --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">{{ $tabs[$activeTab]['label'] }} — Template</div>
        <div style="font-size:12px;color:var(--text3);">Customise the text sent to clients for this notification type.</div>
      </div>

      <div class="fld">
        <label>Email Subject</label>
        <input type="text" name="subject"
               value="{{ old('subject', $t['subject'] ?? '') }}"
               maxlength="200" style="font-family:var(--mono);font-size:13px;">
        <small style="color:var(--text3);font-size:11px;">Use tokens like <code>{company}</code> that are replaced with live values at send time.</small>
      </div>

      <div class="fld">
        <label>Greeting / Salutation</label>
        <input type="text" name="greeting"
               value="{{ old('greeting', $t['greeting'] ?? '') }}"
               maxlength="120" placeholder="Hi {client_name},">
        <small style="color:var(--text3);font-size:11px;">Appears at the top of the email body.</small>
      </div>

      @if($activeTab !== 'job_status')
      <div class="fld">
        <label>Opening Paragraph</label>
        <textarea name="intro" rows="3" maxlength="500"
                  style="font-size:13px;line-height:1.7;">{{ old('intro', $t['intro'] ?? '') }}</textarea>
        <small style="color:var(--text3);font-size:11px;">Shown before the job/quote details table.</small>
      </div>
      @endif

      @if(in_array($activeTab, ['job_created', 'job_status', 'job_invoice']))
      <div class="fld">
        <label>Closing Paragraph</label>
        <textarea name="closing" rows="2" maxlength="500"
                  style="font-size:13px;line-height:1.7;">{{ old('closing', $t['closing'] ?? '') }}</textarea>
        <small style="color:var(--text3);font-size:11px;">Appears after the job details table.</small>
      </div>
      @endif

      @if($activeTab === 'job_created')
      <div class="fld">
        <label>Sign-off Line</label>
        <input type="text" name="sign_off"
               value="{{ old('sign_off', $t['sign_off'] ?? '') }}"
               maxlength="120" placeholder="Thank you for your business!">
      </div>
      @endif

      @if($activeTab === 'quote_sent')
      <div class="fld">
        <label>Validity &amp; Call-to-Action</label>
        <textarea name="validity_note" rows="2" maxlength="500"
                  style="font-size:13px;line-height:1.7;">{{ old('validity_note', $t['validity_note'] ?? '') }}</textarea>
        <small style="color:var(--text3);font-size:11px;">Appears after the quote totals. Use <code>{validity_days}</code> for the configured quote validity period.</small>
      </div>
      @endif

      <div style="margin-top:8px;display:flex;gap:8px;align-items:center;">
        <button type="submit" class="btn btn-primary">Save Template</button>
        <button type="button" class="btn btn-secondary" onclick="resetTemplate()">Reset to Default</button>
      </div>
    </div>

    {{-- Separate form for reset (avoids _method collision) --}}
    <form id="reset-form" method="POST" action="{{ route('bms.settings.email-templates.reset', $activeTab) }}" style="display:none;">
      @csrf
    </form>

    {{-- Token reference & notes panel --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

      <div class="card">
        <div class="card-header"><div class="card-title">Available Tokens</div></div>
        <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">
          Insert these tokens in any field — they are replaced with live values when the email is sent. Click to copy.
        </p>
        @php
          $allTokens = [
            '{company}'        => 'Your company name',
            '{client_name}'    => "Client's full name",
            '{job_id}'         => 'Job reference number',
            '{job_title}'      => 'Job description/title',
            '{stage}'          => 'Current job stage (e.g. Printing)',
            '{quote_number}'   => 'Quote reference (QT-0001)',
            '{validity_days}'  => 'Quote validity period (days)',
          ];
          $tokensByType = [
            'job_created'  => ['{company}', '{client_name}', '{job_id}', '{job_title}'],
            'job_status'   => ['{company}', '{client_name}', '{job_id}', '{stage}'],
            'job_invoice'  => ['{company}', '{client_name}', '{job_id}', '{job_title}'],
            'quote_sent'   => ['{company}', '{client_name}', '{quote_number}', '{validity_days}'],
          ];
        @endphp
        @foreach($tokensByType[$activeTab] as $token)
        <div style="margin-bottom:10px;display:flex;align-items:center;gap:8px;">
          <code style="background:var(--bg3);padding:3px 8px;border-radius:4px;font-size:12px;color:var(--accent);
                       cursor:pointer;user-select:all;flex-shrink:0;"
                onclick="copyToken(this)"
                title="Click to copy">{{ $token }}</code>
          <span style="font-size:12px;color:var(--text3);">{{ $allTokens[$token] ?? '' }}</span>
        </div>
        @endforeach
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">What can't be changed here</div></div>
        <p style="font-size:12px;color:var(--text3);line-height:1.8;">
          The email header and footer (logo, company address, phone) come from your
          <a href="{{ route('bms.settings.branding') }}" style="color:var(--accent);">Branding</a>
          and <a href="{{ route('bms.settings.company') }}" style="color:var(--accent);">Company</a> settings.<br><br>
          The job or quote detail table in the email body is always generated from live data.
          @if($activeTab === 'job_status')
          <br><br>Stage-specific messages (e.g. "Your design is ready for approval") are generated automatically based on the job stage.
          @endif
          @if($activeTab === 'quote_sent')
          <br><br>The quote validity period (number of days) is set under
          <a href="{{ route('bms.settings.invoice') }}" style="color:var(--accent);">Invoice Settings</a>.
          @endif
        </p>
      </div>

    </div>

  </div>
</form>

<script>
function resetTemplate() {
  if (confirm('Reset this template to system defaults? Your changes will be lost.')) {
    document.getElementById('reset-form').submit();
  }
}
function copyToken(el) {
  var text = el.textContent.trim();
  navigator.clipboard.writeText(text).then(function() {
    var origBg = el.style.background, origColor = el.style.color;
    el.style.background = 'var(--accent)';
    el.style.color = '#fff';
    setTimeout(function() {
      el.style.background = origBg;
      el.style.color = 'var(--accent)';
    }, 800);
  });
}
</script>
@endsection
