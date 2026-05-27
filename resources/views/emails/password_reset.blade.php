@extends('emails._layout')

@section('content')
<h2>Reset Your Password</h2>
<p>Hi {{ $name ?? 'there' }},</p>
<p>We received a request to reset the password for your account. Click the button below to set a new password.</p>

<p style="text-align:center;margin:28px 0;">
  <a href="{{ $url }}" class="btn">Reset Password</a>
</p>

<p>This link will expire in <strong>60 minutes</strong>.</p>

<div class="note">
  If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.
</div>

<hr>
<p style="font-size:12px;color:#64748b;">
  If the button above doesn't work, copy and paste this URL into your browser:<br>
  <a href="{{ $url }}" style="color:{{ $settings['brand_color'] ?? '#b91c1c' }};word-break:break-all;">{{ $url }}</a>
</p>
@endsection
