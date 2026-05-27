<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $client->name }} · Job Status</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#0f1117;--bg2:#161b22;--bg3:#1c2230;--border:#2a3040;--text:#e8eaf0;--text2:#a8b0c0;--text3:#606878;--accent:#4f9cf9;--green:#22c55e;--yellow:#facc15;--red:#ef4444}
    body{font-family:'IBM Plex Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:40px 20px;}
    .wrap{max-width:800px;margin:0 auto;}
    .header{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:28px 32px;margin-bottom:24px;}
    .company{font-size:22px;font-weight:700;color:var(--accent);}
    .client{font-size:16px;color:var(--text2);margin-top:4px;}
    .card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:24px;margin-bottom:16px;}
    table{width:100%;border-collapse:collapse;}
    th{text-align:left;padding:8px 12px;font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);}
    td{padding:12px;font-size:13px;border-bottom:1px solid var(--border);}
    tr:last-child td{border-bottom:none;}
    .badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase;background:var(--bg3);color:var(--text3);}
    .stage-waiting,.stage-designing,.stage-approval{background:rgba(79,156,249,.15);color:#4f9cf9;}
    .stage-printing,.stage-fabrication{background:rgba(250,204,21,.15);color:#facc15;}
    .stage-ready,.stage-installed{background:rgba(34,197,94,.15);color:#22c55e;}
    .stage-paid{background:rgba(34,197,94,.2);color:#22c55e;}
    .mono{font-family:'IBM Plex Mono',monospace;}
    .footer{text-align:center;color:var(--text3);font-size:12px;margin-top:40px;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="company">QuickPrints</div>
    <div class="client">Client Portal · {{ $client->name }}</div>
    @if($portal->expires_at)
      <div style="font-size:12px;color:var(--text3);margin-top:8px;">Access expires: {{ \Carbon\Carbon::parse($portal->expires_at)->format('d M Y') }}</div>
    @endif
  </div>

  <div class="card">
    <div style="font-size:13px;font-weight:600;margin-bottom:4px;">{{ $client->name }}</div>
    @if($client->email) <div style="font-size:12px;color:var(--text3);">{{ $client->email }}</div> @endif
    @if($client->phone) <div style="font-size:12px;color:var(--text3);">{{ $client->phone }}</div> @endif
  </div>

  <div class="card">
    <div style="font-size:14px;font-weight:600;margin-bottom:16px;">Your Jobs ({{ $jobs->count() }})</div>
    @if($jobs->count())
      <table>
        <thead>
          <tr><th>Job ID</th><th>Title</th><th>Stage</th><th>Deadline</th><th>Amount</th><th>Paid</th></tr>
        </thead>
        <tbody>
          @foreach($jobs as $job)
            <tr>
              <td class="mono" style="font-size:12px;color:var(--accent);">{{ $job->id }}</td>
              <td style="font-weight:500;">{{ $job->title }}</td>
              <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
              <td style="font-size:12px;color:var(--yellow);">{{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}</td>
              <td style="font-size:13px;">KES {{ number_format($job->amount) }}</td>
              <td>
                @if($job->paid)
                  <span class="badge stage-paid">Paid</span>
                @else
                  <span class="badge" style="background:rgba(239,68,68,.15);color:var(--red);">Unpaid</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:var(--text3);text-align:center;padding:20px 0;">No jobs found for your account.</p>
    @endif
  </div>

  <div class="footer">Powered by QuickPrints BMS &mdash; For queries contact your account manager</div>
</div>
</body>
</html>
