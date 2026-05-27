<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{margin:0;padding:0;background:#f1f5f9;font-family:Arial,'Helvetica Neue',sans-serif;color:#1e293b;}
    .outer{padding:32px 16px;}
    .wrap{max-width:580px;margin:0 auto;}
    .header{background:{{ $settings['brand_color'] ?? '#b91c1c' }};padding:24px 32px;border-radius:8px 8px 0 0;}
    .header-name{color:#ffffff;font-size:20px;font-weight:700;letter-spacing:1px;}
    .header-tagline{color:rgba(255,255,255,0.72);font-size:12px;margin-top:4px;}
    .body{background:#ffffff;padding:32px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;}
    .footer{background:#f8fafc;border:1px solid #e2e8f0;border-top:none;padding:16px 32px;border-radius:0 0 8px 8px;text-align:center;font-size:11px;color:#94a3b8;line-height:1.9;}
    h2{color:#0f172a;font-size:18px;margin:0 0 16px;font-weight:700;}
    p{color:#374151;font-size:14px;line-height:1.8;margin:0 0 14px;}
    a.btn{display:inline-block;padding:12px 28px;background:{{ $settings['brand_color'] ?? '#b91c1c' }};color:#ffffff !important;text-decoration:none;border-radius:6px;font-weight:700;font-size:14px;margin:8px 0;}
    hr{border:none;border-top:1px solid #e5e7eb;margin:24px 0;}
    table.info{width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;}
    table.info td{padding:9px 12px;border-bottom:1px solid #f1f5f9;vertical-align:top;}
    table.info tr:last-child td{border-bottom:none;}
    table.info .lbl{color:#64748b;width:130px;}
    table.info .val{font-weight:600;color:#0f172a;}
    table.items{width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;}
    table.items th{background:#f8fafc;padding:9px 12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e5e7eb;}
    table.items td{padding:10px 12px;border-bottom:1px solid #f1f5f9;color:#374151;}
    table.items .num{text-align:right;font-family:monospace;}
    .badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;letter-spacing:.5px;}
    .badge-paid{background:#d1fae5;color:#065f46;}
    .badge-unpaid{background:#fee2e2;color:#991b1b;}
    .badge-stage{background:#dbeafe;color:#1e40af;}
    .note{background:#f8fafc;border-left:3px solid {{ $settings['brand_color'] ?? '#b91c1c' }};padding:12px 16px;border-radius:0 6px 6px 0;font-size:13px;color:#374151;margin:16px 0;}
    .total-line{font-size:15px;font-weight:700;border-top:2px solid #e5e7eb;padding-top:12px;margin-top:8px;color:#0f172a;}
  </style>
</head>
<body>
<div class="outer">
  <div class="wrap">

    <div class="header">
      @if(!empty($settings['logo_url']))
        <img src="{{ $settings['logo_url'] }}" style="height:36px;display:block;margin-bottom:8px;" alt="">
      @endif
      <div class="header-name">{{ strtoupper($settings['company_name'] ?? 'QUICK PRINTS') }}</div>
      @if(!empty($settings['company_tagline']))
        <div class="header-tagline">{{ $settings['company_tagline'] }}</div>
      @endif
    </div>

    <div class="body">
      @yield('content')
    </div>

    <div class="footer">
      <strong>{{ $settings['company_name'] ?? 'Quick Prints' }}</strong>
      @if(!empty($settings['address'])) &nbsp;&middot;&nbsp; {{ $settings['address'] }}@endif
      @if(!empty($settings['phone'])) &nbsp;&middot;&nbsp; {{ $settings['phone'] }}@endif
      @if(!empty($settings['email'])) &nbsp;&middot;&nbsp; {{ $settings['email'] }}@endif
      @if(!empty($settings['website'])) &nbsp;&middot;&nbsp; {{ $settings['website'] }}@endif
      <br>This is an automated email — please do not reply directly to this message.
    </div>

  </div>
</div>
</body>
</html>
