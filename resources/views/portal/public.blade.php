<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="{{ $brand['primary'] }}">
  <title>{{ $settings['company_name'] ?? 'QuickPrints' }} · Client Portal</title>
  @if(!empty($settings['favicon_url']))
    <link rel="icon" href="{{ $settings['favicon_url'] }}">
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --accent:{{ $brand['primary'] }};
      --accent2:{{ $brand['secondary'] }};
      --accent-rgb:{{ $brand['rgb'] }};
      --accent-dim:rgba(var(--accent-rgb),.14);
      --accent-a06:rgba(var(--accent-rgb),.06);
      --accent-a10:rgba(var(--accent-rgb),.10);
      --accent-a18:rgba(var(--accent-rgb),.18);
      --accent-a25:rgba(var(--accent-rgb),.25);
      --bg:#0c0e13;--bg2:#13161d;--bg3:#1a1e27;--bg4:#222733;
      --border:#2a3040;--border2:#383f50;
      --text:#e8eaf0;--text2:#9ba3b8;--text3:#5c6478;
      --green:#22c55e;--green-dim:rgba(34,197,94,.14);
      --yellow:#eab308;--yellow-dim:rgba(234,179,8,.14);
      --red:#ef4444;--red-dim:rgba(239,68,68,.14);
      --sidebar-w:220px;--font:'IBM Plex Sans',sans-serif;--mono:'IBM Plex Mono',monospace;
      --content-max:1120px;
      --portal-foot-h:56px;
    }
    body.theme-light{
      --bg:#e8eaef;--bg2:#ffffff;--bg3:#f4f5f8;--bg4:#eceef2;
      --border:#dde1e8;--border2:#c8ced8;
      --text:#141820;--text2:#4b5568;--text3:#8b95a5;
      --accent-dim:rgba(var(--accent-rgb),.10);
      --green-dim:rgba(34,197,94,.10);--yellow-dim:rgba(234,179,8,.12);--red-dim:rgba(239,68,68,.10);
    }
    body{
      font-family:var(--font);background:var(--bg);color:var(--text);min-height:100vh;line-height:1.5;
      -webkit-text-size-adjust:100%;
      padding:env(safe-area-inset-top) env(safe-area-inset-right) 0 env(safe-area-inset-left);
      background-image:
        radial-gradient(ellipse 80% 50% at 0% 0%, var(--accent-a10), transparent 55%),
        radial-gradient(ellipse 60% 40% at 100% 100%, var(--accent-a06), transparent 50%);
    }
    a{color:inherit;text-decoration:none}
    button{font-family:var(--font);cursor:pointer;border:none;background:none}

    .shell{
      min-height:100vh;
      max-width:var(--content-max);
      margin:0 auto;
      padding-bottom:calc(var(--portal-foot-h) + env(safe-area-inset-bottom));
    }

    /* ── Mobile sidebar header ── */
    .sidebar{display:none}
    .sidebar-accent{height:3px;background:linear-gradient(90deg,var(--accent),var(--accent2));flex-shrink:0}
    .sidebar-top{
      padding:14px 16px 12px;border-bottom:1px solid var(--border);flex-shrink:0;
    }
    .sidebar-top--desktop{display:none}
    .sidebar-brand{padding:0;border-bottom:none;width:100%;min-width:0}
    .sidebar-brand img,.sidebar-brand-img{height:32px;max-width:100%;width:auto;object-fit:contain;display:block}
    .sidebar-brand h1{font-size:15px;font-weight:700;color:var(--accent);letter-spacing:-.01em;line-height:1.2}
    .sidebar-top--mobile .sidebar-brand{flex:1;min-width:0}
    .sidebar-top--mobile .sidebar-brand img{max-width:calc(100% - 8px)}
    .sidebar-brand p{font-size:10px;color:var(--text3);margin-top:4px;line-height:1.3}

    .sidebar-client{
      padding:16px 16px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
    }
    .sidebar-client--mobile{display:none}
    .portal-title-mobile{display:none}
    .client-greeting{
      font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;
      color:var(--text3);margin-bottom:5px;
    }
    .client-name{
      font-size:20px;font-weight:700;line-height:1.15;letter-spacing:-.03em;
      color:var(--accent);word-break:break-word;
    }

    .sidebar-stats{
      display:grid;grid-template-columns:repeat(3,1fr);gap:6px;padding:10px 12px;
      border-bottom:1px solid var(--border);flex-shrink:0;
    }
    .stat-mini{background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:8px 6px;text-align:center}
    .stat-mini-lbl{font-size:8px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.05em}
    .stat-mini-val{font-size:12px;font-weight:700;margin-top:3px;font-family:var(--mono);line-height:1.2}
    .stat-mini-val.accent{color:var(--accent)}
    .stat-mini-val.due{color:var(--red)}

    .portal-foot{
      position:fixed;bottom:0;left:0;right:0;z-index:45;
      padding:12px 20px;
      padding-bottom:max(12px, env(safe-area-inset-bottom));
      border-top:1px solid var(--border);
      background:var(--bg2);
      box-shadow:0 -4px 24px rgba(0,0,0,.2);
    }
    body.theme-light .portal-foot{box-shadow:0 -4px 20px rgba(0,0,0,.08)}
    .portal-foot-inner{
      width:100%;max-width:var(--content-max);margin:0 auto;
      display:flex;align-items:center;justify-content:center;
    }
    .portal-foot .contact-icons{margin-bottom:0;justify-content:center}
    .contact-icons{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
    .contact-ico{
      width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--bg3);
      display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--text2);
      transition:all .15s;
    }
    .contact-ico:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-a06)}
    .theme-btn{
      width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--bg3);
      color:var(--text2);font-size:15px;display:flex;align-items:center;justify-content:center;
      flex-shrink:0;transition:all .15s;touch-action:manipulation;-webkit-tap-highlight-color:transparent;
    }
    .theme-btn:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-a06)}

    /* ── Main ── */
    .main{
      min-width:0;padding:28px 36px 32px;
    }
    .main-hd{
      display:flex;align-items:center;justify-content:space-between;gap:20px;
      margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border);
    }
    .main-hd-brand{display:flex;align-items:center;flex-shrink:0;min-width:0}
    .main-hd-brand img{height:36px;max-width:180px;object-fit:contain;display:block}
    .main-hd-brand h1{font-size:16px;font-weight:700;color:var(--accent);letter-spacing:-.01em;line-height:1.2}
    .main-hd-left{min-width:0;flex:1}
    .main-hd-left h2{font-size:24px;font-weight:700;letter-spacing:-.03em}
    .main-hd-left p{font-size:13px;color:var(--text3);margin-top:4px}
    .main-hd-right{
      display:flex;align-items:center;gap:12px;flex-shrink:0;text-align:right;
    }
    .main-hd-client{min-width:0}
    .main-hd-client .client-greeting{margin-bottom:4px}
    .main-hd-client .client-name{font-size:18px;line-height:1.2}
    .theme-btn--desktop{display:flex}
    .theme-btn--mobile{display:none}

    .portal-stats{
      display:grid;grid-template-columns:repeat(3,1fr);gap:10px;
      margin-bottom:24px;
    }
    .portal-stats .stat-mini{padding:12px 10px}
    .portal-stats .stat-mini-lbl{font-size:9px}
    .portal-stats .stat-mini-val{font-size:14px;margin-top:4px}

    /* Your Jobs table */
    .jobs-card{
      background:var(--bg2);border:1px solid var(--border);border-radius:12px;overflow:hidden;
    }
    .jobs-card-hd{padding:18px 20px;font-size:15px;font-weight:700;border-bottom:1px solid var(--border)}
    .jobs-table{width:100%;border-collapse:collapse}
    .jobs-table th{
      text-align:left;padding:10px 16px;font-size:10px;font-weight:600;color:var(--text3);
      text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--border);
      background:var(--bg3);
    }
    .jobs-table td{
      padding:14px 16px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle;
    }
    .jobs-table tr:last-child td{border-bottom:none}
    .jobs-table tbody tr:hover td{background:var(--accent-a06)}
    .job-link{font-family:var(--mono);font-size:12px;font-weight:600;color:var(--accent);text-decoration:none}
    .job-link:hover{text-decoration:underline}
    .job-title-cell{font-weight:600;color:var(--text)}
    .job-deadline{font-size:12px;color:var(--yellow);opacity:.85}
    body.theme-light .job-deadline{color:#a16207;opacity:1}
    .job-amt{font-size:13px;color:var(--text);white-space:nowrap}

    .badge{
      display:inline-block;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:600;
      text-transform:uppercase;letter-spacing:.04em;line-height:1.2;
    }
    .badge-stage-waiting,.badge-stage-designing,.badge-stage-approval{
      background:rgba(59,130,246,.12);color:#60a5fa;
    }
    body.theme-light .badge-stage-waiting,body.theme-light .badge-stage-designing,body.theme-light .badge-stage-approval{
      background:rgba(59,130,246,.10);color:#2563eb;
    }
    .badge-stage-printing,.badge-stage-fabrication{
      background:rgba(234,179,8,.12);color:#fbbf24;
    }
    body.theme-light .badge-stage-printing,body.theme-light .badge-stage-fabrication{
      background:rgba(234,179,8,.12);color:#b45309;
    }
    .badge-stage-ready,.badge-stage-installed,.badge-stage-paid{
      background:rgba(34,197,94,.12);color:#4ade80;
    }
    body.theme-light .badge-stage-ready,body.theme-light .badge-stage-installed,body.theme-light .badge-stage-paid{
      background:rgba(34,197,94,.10);color:#15803d;
    }
    .badge-paid{background:rgba(34,197,94,.12);color:#4ade80}
    body.theme-light .badge-paid{background:rgba(34,197,94,.10);color:#15803d}
    .badge-unpaid{background:rgba(239,68,68,.12);color:#f87171}
    body.theme-light .badge-unpaid{background:rgba(239,68,68,.10);color:#b91c1c}

    .jobs-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}

    /* Mobile job cards (shown instead of table on small screens) */
    .jobs-cards{display:none;flex-direction:column;gap:10px;padding:12px}
    .job-card{
      background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:14px;
      display:flex;flex-direction:column;gap:10px;
    }
    .job-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
    .job-card-title{font-size:14px;font-weight:600;line-height:1.35;flex:1;min-width:0}
    .job-card-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px 12px}
    .job-card-field{font-size:11px}
    .job-card-lbl{font-size:9px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
    .job-card-val{font-size:12px;font-weight:500;color:var(--text2)}
    .job-card-foot{display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:8px;border-top:1px solid var(--border)}
    .job-card-amt{font-family:var(--mono);font-size:14px;font-weight:700}
    .job-card-link{
      display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;
      border-radius:8px;font-size:12px;font-weight:600;background:var(--accent);color:#fff;
      touch-action:manipulation;
    }
    .job-card-link:hover{opacity:.92;color:#fff}

    .main-tabs{
      display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:0;
      overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;
    }
    .main-tabs::-webkit-scrollbar{display:none}
    .main-tab{
      padding:12px 18px;font-size:13px;font-weight:600;color:var(--text3);background:none;
      border-bottom:2px solid transparent;margin-bottom:-1px;transition:all .15s;white-space:nowrap;
      flex:1;text-align:center;min-height:44px;touch-action:manipulation;-webkit-tap-highlight-color:transparent;
    }
    .main-tab:hover{color:var(--text2)}
    .main-tab.active{color:var(--accent);border-bottom-color:var(--accent)}
    .main-panel{display:none}
    .main-panel.active{display:block}

    .inv-list{display:flex;flex-direction:column;gap:10px}
    .inv-row{
      display:grid;grid-template-columns:1fr auto auto;gap:14px;align-items:center;
      padding:16px 18px;background:var(--bg2);border:1px solid var(--border);border-radius:12px;
      transition:border-color .15s,box-shadow .15s;
    }
    .inv-row:hover{border-color:var(--accent-a25);box-shadow:0 4px 16px var(--accent-a08)}
    .inv-row-id{font-family:var(--mono);font-size:11px;font-weight:600;color:var(--accent)}
    .inv-row-title{font-size:14px;font-weight:600;margin-top:4px}
    .inv-row-sub{font-size:11px;color:var(--text3);margin-top:3px}
    .inv-row-amt{text-align:right;font-family:var(--mono);font-size:14px;font-weight:700}
    .inv-row-amt small{display:block;font-size:10px;color:var(--text3);font-weight:500;margin-top:2px}
    .inv-actions{display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end}
    .btn-inv{
      padding:10px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;
      border:1px solid var(--border);background:var(--bg3);color:var(--text2);white-space:nowrap;
      transition:all .15s;min-height:40px;display:inline-flex;align-items:center;justify-content:center;
      touch-action:manipulation;-webkit-tap-highlight-color:transparent;
    }
    .btn-inv:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-a06)}
    .btn-inv.primary{background:var(--accent);border-color:var(--accent);color:#fff}
    .btn-inv.primary:hover{opacity:.92;color:#fff}

    .empty-state{
      text-align:center;padding:60px 24px;background:var(--bg2);border:1px dashed var(--border);
      border-radius:16px;color:var(--text3);
    }
    .empty-state .ico{font-size:48px;margin-bottom:16px;opacity:.4}

    /* ── Tablet & mobile ── */
    @media(max-width:860px){
      .shell{max-width:none;margin:0}
      .sidebar{
        display:flex;flex-direction:column;
        width:100%;background:var(--bg2);
        position:sticky;top:0;z-index:40;
        border-bottom:1px solid var(--border);
        box-shadow:0 4px 20px rgba(0,0,0,.18);
      }
      body.theme-light .sidebar{box-shadow:0 4px 16px rgba(0,0,0,.08)}
      .portal-foot{padding:12px 16px}
      .main{
        padding:20px 16px 24px;
      }
      .main-hd{display:none}
      .portal-title-mobile{
        display:block;font-size:18px;font-weight:700;letter-spacing:-.03em;
        color:var(--text);margin-bottom:8px;line-height:1.2;
      }
      .portal-title-mobile span{
        display:block;font-size:11px;font-weight:500;color:var(--text3);
        letter-spacing:0;margin-top:3px;
      }
      .sidebar-top--mobile{
        display:flex;align-items:center;justify-content:space-between;gap:10px;
        padding:12px 14px 10px;border-bottom:1px solid var(--border);
      }
      .sidebar-client--mobile{display:block}
      .portal-stats{display:none}
      .main-tabs{
        margin-top:0;
        margin-left:-16px;margin-right:-16px;padding-left:16px;padding-right:16px;
        margin-bottom:20px;
      }
      .inv-row{grid-template-columns:1fr;gap:12px;padding:14px 16px}
      .inv-row-amt{text-align:left}
      .inv-actions{justify-content:stretch}
      .inv-actions .btn-inv{flex:1 1 calc(50% - 3px);min-width:0}
      .theme-btn--desktop{display:none}
      .theme-btn--mobile{display:flex}
      .sidebar-client--mobile{padding:12px 14px 10px}
      .sidebar-client--mobile .portal-title-mobile{margin-bottom:10px}
      .welcome-row{
        display:flex;align-items:center;justify-content:space-between;gap:12px;
      }
      .welcome-row .client-greeting{margin-bottom:0;flex-shrink:0}
      .welcome-row .client-name{
        font-size:17px;text-align:right;flex:1;min-width:0;
      }
      .sidebar-stats{padding:8px 10px 10px;border-bottom:none}
    }

    @media(max-width:640px){
      .jobs-card-hd{padding:14px 16px;font-size:14px}
      .jobs-scroll{display:none}
      .jobs-cards{display:flex}
      .jobs-table{display:none}
      .contact-ico{width:36px;height:36px;font-size:14px}
      .stat-mini-val{font-size:11px}
      .stat-mini-lbl{font-size:7px}
    }

    @media(max-width:400px){
      .sidebar-top--mobile{padding:10px 12px 8px}
      .sidebar-client--mobile{padding:8px 12px}
      .sidebar-stats{gap:4px;padding:6px 8px 8px}
      .stat-mini{padding:6px 4px}
      .main{padding:16px 12px 20px}
      .main-tabs{margin-left:-12px;margin-right:-12px;padding-left:12px;padding-right:12px}
      .portal-title-mobile{font-size:17px}
      .welcome-row .client-name{font-size:16px}
      .job-card-meta{grid-template-columns:1fr}
      .inv-actions .btn-inv{flex:1 1 100%}
      .empty-state{padding:40px 16px}
    }
  </style>
</head>
<body>
@php
  use App\Support\BrandAssets;
  $stages = [
    ['key' => 'waiting',     'label' => 'Queue'],
    ['key' => 'designing',   'label' => 'Design'],
    ['key' => 'approval',    'label' => 'Approve'],
    ['key' => 'printing',    'label' => 'Print'],
    ['key' => 'fabrication', 'label' => 'Fab'],
    ['key' => 'ready',       'label' => 'Ready'],
    ['key' => 'installed',   'label' => 'Install'],
    ['key' => 'paid',        'label' => 'Paid'],
  ];
  $stageKeys = array_column($stages, 'key');
  $stageIndex = fn ($stage) => max(0, array_search($stage, $stageKeys, true) ?: 0);
  $currency = $settings['currency_symbol'] ?? ($settings['currency'] ?? 'KES');
  $inProgress = $jobs->whereNotIn('stage', ['ready', 'installed', 'paid'])->count();
  $totalAmount = $jobs->sum('amount');
  $unpaidTotal = $jobs->where('paid', false)->sum('amount');
  $brandLogos = BrandAssets::logos($settings);
  $hasLogo = BrandAssets::hasLogo($settings);
@endphp

<div class="shell">
  <aside class="sidebar">
    <div class="sidebar-accent"></div>

    <div class="sidebar-top sidebar-top--mobile">
      <div class="sidebar-brand">
        @if($hasLogo)
          <img
            class="sidebar-brand-img sidebar-brand-img--mobile"
            src="{{ BrandAssets::logoForTheme($settings, $defaultTheme) }}"
            alt="{{ $settings['company_name'] ?? 'Logo' }}"
          >
        @else
          <h1>{{ $settings['company_name'] ?? 'QuickPrints' }}</h1>
        @endif
      </div>
      <button type="button" class="theme-btn theme-btn--mobile" title="Toggle theme" aria-label="Toggle theme">☀</button>
    </div>

    <div class="sidebar-client sidebar-client--mobile">
      <div class="portal-title-mobile">
        Client Portal
        <span>{{ $settings['company_name'] ?? 'QuickPrints' }} · {{ $jobs->count() }} job{{ $jobs->count() === 1 ? '' : 's' }}</span>
      </div>
      <div class="welcome-row">
        <div class="client-greeting">Welcome</div>
        <div class="client-name">{{ $client->name }}</div>
      </div>
    </div>

    <div class="sidebar-stats">
      <div class="stat-mini">
        <div class="stat-mini-lbl">Jobs</div>
        <div class="stat-mini-val accent">{{ $jobs->count() }}</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-lbl">Active</div>
        <div class="stat-mini-val">{{ $inProgress }}</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-lbl">Due</div>
        <div class="stat-mini-val due">{{ $currency }} {{ number_format($unpaidTotal / 1000, 0) }}k</div>
      </div>
    </div>
  </aside>

  <main class="main">
    <div class="main-hd">
      <div class="main-hd-brand">
        @if($hasLogo)
          <img
            id="portal-brand-logo"
            src="{{ BrandAssets::logoForTheme($settings, $defaultTheme) }}"
            alt="{{ $settings['company_name'] ?? 'Logo' }}"
            data-logo-dark="{{ $brandLogos['dark'] ?? '' }}"
            data-logo-light="{{ $brandLogos['light'] ?? '' }}"
          >
        @else
          <h1>{{ $settings['company_name'] ?? 'QuickPrints' }}</h1>
        @endif
      </div>
      <div class="main-hd-left">
        <h2>Client Portal</h2>
        <p>{{ $settings['company_name'] ?? 'QuickPrints' }} · {{ $jobs->count() }} job{{ $jobs->count() === 1 ? '' : 's' }}</p>
      </div>
      <div class="main-hd-right">
        <div class="main-hd-client">
          <div class="client-greeting">Welcome</div>
          <div class="client-name">{{ $client->name }}</div>
        </div>
        <button type="button" class="theme-btn theme-btn--desktop" title="Toggle theme" aria-label="Toggle theme">☀</button>
      </div>
    </div>

    <div class="portal-stats">
      <div class="stat-mini">
        <div class="stat-mini-lbl">Jobs</div>
        <div class="stat-mini-val accent">{{ $jobs->count() }}</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-lbl">Active</div>
        <div class="stat-mini-val">{{ $inProgress }}</div>
      </div>
      <div class="stat-mini">
        <div class="stat-mini-lbl">Due</div>
        <div class="stat-mini-val due">{{ $currency }} {{ number_format($unpaidTotal / 1000, 0) }}k</div>
      </div>
    </div>

    <div class="main-tabs" role="tablist">
      <button type="button" class="main-tab active" data-panel="jobs" role="tab">Your Jobs</button>
      <button type="button" class="main-tab" data-panel="invoices" role="tab">Invoices</button>
    </div>

    <section class="main-panel active" id="panel-jobs" role="tabpanel">
    @if($jobs->count())
      <div class="jobs-card">
        <div class="jobs-card-hd">Your Jobs ({{ $jobs->count() }})</div>
        <div class="jobs-scroll">
          <table class="jobs-table">
        <thead>
              <tr>
                <th>Job ID</th>
                <th>Title</th>
                <th>Stage</th>
                <th>Deadline</th>
                <th>Amount</th>
                <th>Paid</th>
              </tr>
        </thead>
        <tbody>
          @foreach($jobs as $job)
            <tr>
                  <td>
                    <a href="{{ route('portal.public.invoice', [$portal->token, $job->id]) }}" class="job-link" target="_blank">{{ $job->id }}</a>
                  </td>
                  <td class="job-title-cell">{{ $job->title }}</td>
                  <td><span class="badge badge-stage-{{ $job->stage }}">{{ str_replace('_', ' ', $job->stage) }}</span></td>
                  <td class="job-deadline">{{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}</td>
                  <td class="job-amt">{{ $currency }} {{ number_format($job->amount) }}</td>
              <td>
                @if($job->paid)
                      <span class="badge badge-paid">Paid</span>
                @else
                      <span class="badge badge-unpaid">Unpaid</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
        </div>
        <div class="jobs-cards">
          @foreach($jobs as $job)
            <article class="job-card">
              <div class="job-card-top">
                <div class="job-card-title">{{ $job->title }}</div>
                <a href="{{ route('portal.public.invoice', [$portal->token, $job->id]) }}" class="job-link" target="_blank">{{ $job->id }}</a>
              </div>
              <div class="job-card-meta">
                <div class="job-card-field">
                  <div class="job-card-lbl">Stage</div>
                  <div class="job-card-val"><span class="badge badge-stage-{{ $job->stage }}">{{ str_replace('_', ' ', $job->stage) }}</span></div>
                </div>
                <div class="job-card-field">
                  <div class="job-card-lbl">Deadline</div>
                  <div class="job-card-val job-deadline">{{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}</div>
                </div>
              </div>
              <div class="job-card-foot">
                <div>
                  <div class="job-card-lbl">Amount</div>
                  <div class="job-card-amt">{{ $currency }} {{ number_format($job->amount) }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                  @if($job->paid)
                    <span class="badge badge-paid">Paid</span>
                  @else
                    <span class="badge badge-unpaid">Unpaid</span>
                  @endif
                  <a href="{{ route('portal.public.invoice', [$portal->token, $job->id]) }}" class="job-card-link" target="_blank">View</a>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      </div>
    @else
      <div class="empty-state">
        <div class="ico">📋</div>
        <p>No jobs on your account yet.</p>
      </div>
      @endif
    </section>

    <section class="main-panel" id="panel-invoices" role="tabpanel">
      @if($jobs->count())
        <div class="inv-list">
          @foreach($jobs as $job)
            <article class="inv-row">
              <div>
                <div class="inv-row-id">{{ $job->id }}</div>
                <div class="inv-row-title">{{ $job->title }}</div>
                <div class="inv-row-sub">
                  {{ $job->deadline ? 'Due '.$job->deadline->format('d M Y') : 'No due date' }}
                  @if($job->branch) · {{ $job->branch }} @endif
                </div>
              </div>
              <div class="inv-row-amt">
                {{ $currency }} {{ number_format($job->amount) }}
                <small>{{ $job->paid ? 'Paid' : 'Unpaid' }}</small>
              </div>
              <div class="inv-actions">
                <a href="{{ route('portal.public.invoice', [$portal->token, $job->id]) }}" class="btn-inv primary" target="_blank">View</a>
                <a href="{{ route('portal.public.invoice.pdf', [$portal->token, $job->id]) }}" class="btn-inv" target="_blank">PDF</a>
                @if($job->paid)
                  <a href="{{ route('portal.public.receipt.pdf', [$portal->token, $job->id]) }}" class="btn-inv" target="_blank">Receipt</a>
    @endif
  </div>
            </article>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <div class="ico">🧾</div>
          <p>No invoices yet.</p>
        </div>
      @endif
    </section>
  </main>

  <footer class="portal-foot">
    <div class="portal-foot-inner">
      <div class="contact-icons">
        @if(!empty($settings['phone']))
          <a class="contact-ico" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}" title="{{ $settings['phone'] }}">☎</a>
        @endif
        @if(!empty($settings['email']))
          <a class="contact-ico" href="mailto:{{ $settings['email'] }}" title="{{ $settings['email'] }}">✉</a>
        @endif
        @if(!empty($settings['website']))
          <a class="contact-ico" href="{{ str_starts_with($settings['website'], 'http') ? $settings['website'] : 'https://'.$settings['website'] }}" target="_blank" rel="noopener" title="{{ $settings['website'] }}">↗</a>
        @endif
      </div>
    </div>
  </footer>
</div>

<script>
(function () {
  const storageKey = 'qp-portal-theme-{{ $portal->token }}';
  const portalDefault = 'dark';
  const body = document.body;
  const themeBtns = document.querySelectorAll('.theme-btn');

  function applyTheme(theme) {
    const isLight = theme === 'light';
    body.classList.toggle('theme-light', isLight);
    themeBtns.forEach(function (btn) {
      btn.textContent = isLight ? '🌙' : '☀';
      btn.title = isLight ? 'Switch to dark mode' : 'Switch to light mode';
    });
    const logo = document.getElementById('portal-brand-logo');
    if (logo) {
      const dark = logo.dataset.logoDark;
      const light = logo.dataset.logoLight;
      logo.src = isLight ? (light || dark) : (dark || light);
    }
    const mobileLogo = document.querySelector('.sidebar-brand-img--mobile');
    if (mobileLogo) {
      mobileLogo.src = logo ? logo.src : mobileLogo.src;
    }
  }

  const saved = localStorage.getItem(storageKey);
  const initial = saved === 'light' || saved === 'dark' ? saved : portalDefault;
  applyTheme(initial);

  themeBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const next = body.classList.contains('theme-light') ? 'dark' : 'light';
      localStorage.setItem(storageKey, next);
      applyTheme(next);
    });
  });

  document.querySelectorAll('.main-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      const panel = tab.getAttribute('data-panel');
      document.querySelectorAll('.main-tab').forEach(function (t) { t.classList.remove('active'); });
      document.querySelectorAll('.main-panel').forEach(function (p) { p.classList.remove('active'); });
      tab.classList.add('active');
      document.getElementById('panel-' + panel).classList.add('active');
    });
  });
})();
</script>
</body>
</html>
