@extends('emails._layout')

@section('content')
<h2>Test Email — SMTP Working!</h2>
<p>Hi there,</p>
<p>This is a test email from <strong>{{ $settings['company_name'] ?? 'Quick Prints' }}</strong> BMS to confirm your SMTP configuration is working correctly.</p>

<table class="info">
  <tr>
    <td class="lbl">Sent At</td>
    <td class="val">{{ now()->format('d M Y, H:i:s') }}</td>
  </tr>
  <tr>
    <td class="lbl">SMTP Host</td>
    <td class="val">{{ $settings['email_settings']['host'] ?? 'N/A' }}</td>
  </tr>
  <tr>
    <td class="lbl">From Address</td>
    <td class="val">{{ $settings['email_settings']['from_address'] ?? $settings['email'] ?? 'N/A' }}</td>
  </tr>
</table>

<div class="note">
  Your email settings are configured correctly. Automated notifications for jobs, quotes, and password resets will be delivered to clients and staff.
</div>

<p>You can now enable individual notification types in <strong>Settings → Email</strong>.</p>
@endsection
