@extends('layouts.bms')

@push('styles')
<style>
.dash{margin:-8px -4px 0;}
.dash-hero{
  position:relative;border-radius:14px;padding:24px 28px;margin-bottom:18px;overflow:hidden;
  background:linear-gradient(135deg,var(--brand-dark) 0%,var(--accent) 45%,var(--brand-deep) 100%);
  border:1px solid rgba(255,255,255,.12);box-shadow:0 8px 32px var(--accent-a25);
}
body.theme-light .dash-hero{background:linear-gradient(135deg,var(--brand-deep) 0%,var(--accent) 50%,var(--accent2) 100%);box-shadow:0 8px 28px var(--accent-a18);}
.dash-hero::before{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.06);}
.dash-hero::after{content:'';position:absolute;left:40%;bottom:-60px;width:280px;height:120px;border-radius:50%;background:rgba(0,0,0,.12);filter:blur(20px);}
.dash-hero-inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;gap:20px;align-items:end;}
.dash-hero h1{font-size:13px;font-weight:600;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;}
.dash-hero-title{font-size:26px;font-weight:700;color:#fff;letter-spacing:-.03em;line-height:1.2;}
.dash-hero-sub{font-size:12px;color:rgba(255,255,255,.65);margin-top:6px;}
.dash-hero-metrics{display:flex;gap:28px;flex-wrap:wrap;}
.dash-hero-metric{text-align:right;}
.dash-hero-metric .lbl{font-size:10px;font-weight:600;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.06em;}
.dash-hero-metric .val{font-size:22px;font-weight:700;color:#fff;font-family:var(--mono);margin-top:2px;}
.dash-hero-metric .sub{font-size:10px;color:rgba(255,255,255,.5);margin-top:2px;}
.dash-hero-metric .chg{display:inline-block;font-size:10px;font-weight:700;padding:2px 6px;border-radius:99px;margin-top:4px;}
.dash-hero-metric .chg.up{background:rgba(34,197,94,.25);color:#bbf7d0;}
.dash-hero-metric .chg.down{background:rgba(239,68,68,.25);color:#fecaca;}

.dash-actions{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px;}
.dash-action{
  display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;
  background:var(--bg2);border:1px solid var(--border);text-decoration:none;color:inherit;
  transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.06);
}
.dash-action:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 6px 20px var(--accent-a12);}
.dash-action-ico{
  width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  font-size:18px;flex-shrink:0;background:var(--accent-dim);
}
.dash-action-ico.green{background:var(--green-dim);}
.dash-action-ico.blue{background:var(--blue-dim);}
.dash-action-ico.purple{background:var(--purple-dim);}
.dash-action-txt{font-size:13px;font-weight:600;color:var(--text);}
.dash-action-sub{font-size:10px;color:var(--text3);margin-top:2px;}

.dash-layout{display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start;}
.dash-panel{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:18px;box-shadow:0 1px 3px rgba(0,0,0,.06);}
.dash-panel-hd{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:16px;}
.dash-panel-title{font-size:14px;font-weight:700;color:var(--text);}
.dash-panel-desc{font-size:11px;color:var(--text3);margin-top:2px;}
.dash-tag{font-size:10px;font-weight:600;padding:3px 9px;border-radius:99px;background:var(--bg3);border:1px solid var(--border);color:var(--text3);white-space:nowrap;}

.dash-chart{position:relative;height:280px;}
.dash-chart.sm{height:200px;}

.dash-flow{margin-top:20px;padding-top:18px;border-top:1px solid var(--border);}
.dash-flow-bar{display:flex;height:32px;border-radius:8px;overflow:hidden;background:var(--bg4);margin-bottom:14px;}
.dash-flow-seg{height:100%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;min-width:2px;transition:flex .6s ease;}
.dash-flow-seg:first-child{border-radius:8px 0 0 8px;}
.dash-flow-seg:last-child{border-radius:0 8px 8px 0;}
.dash-flow-stages{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
.dash-flow-stage{
  text-align:center;padding:10px 6px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);
  transition:border-color .15s;
}
.dash-flow-stage:hover{border-color:var(--border2);}
.dash-flow-stage .cnt{font-size:18px;font-weight:700;font-family:var(--mono);color:var(--text);}
.dash-flow-stage .name{font-size:9px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;margin-top:3px;}

.dash-sidebar{display:flex;flex-direction:column;gap:14px;}
.dash-alert{
  display:flex;gap:12px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);
  background:var(--bg3);align-items:flex-start;
}
.dash-alert.warn{border-color:rgba(217,119,6,.3);background:var(--yellow-dim);}
.dash-alert.danger{border-color:rgba(220,38,38,.3);background:var(--red-dim);}
.dash-alert-ico{font-size:16px;flex-shrink:0;margin-top:1px;}
.dash-alert-body{flex:1;min-width:0;}
.dash-alert-title{font-size:12px;font-weight:600;color:var(--text);}
.dash-alert-val{font-size:15px;font-weight:700;font-family:var(--mono);margin-top:2px;}
.dash-alert-val.red{color:var(--red);}
.dash-alert-val.yellow{color:var(--yellow);}
.dash-alert-sub{font-size:10px;color:var(--text3);margin-top:2px;}

.dash-rank{display:flex;flex-direction:column;gap:10px;}
.dash-rank-row{display:grid;grid-template-columns:24px 1fr auto;gap:10px;align-items:center;}
.dash-rank-num{font-size:11px;font-weight:700;color:var(--text3);font-family:var(--mono);text-align:center;}
.dash-rank-num.top{color:var(--accent);}
.dash-rank-info{min-width:0;}
.dash-rank-name{font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dash-rank-bar{height:4px;background:var(--bg4);border-radius:99px;margin-top:5px;overflow:hidden;}
.dash-rank-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--accent),var(--accent2));transition:width .6s ease;}
.dash-rank-amt{font-size:11px;font-family:var(--mono);color:var(--accent);font-weight:600;white-space:nowrap;}

.dash-cat-list{display:flex;flex-direction:column;gap:8px;}
.dash-cat-row{display:grid;grid-template-columns:100px 1fr 60px;gap:10px;align-items:center;}
.dash-cat-name{font-size:11px;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dash-cat-track{height:8px;background:var(--bg4);border-radius:99px;overflow:hidden;}
.dash-cat-fill{height:100%;border-radius:99px;background:var(--accent);opacity:.85;}
.dash-cat-val{font-size:10px;font-family:var(--mono);color:var(--text3);text-align:right;}

.dash-table-wrap{overflow-x:auto;border-radius:10px;border:1px solid var(--border);}
.dash-table{width:100%;border-collapse:collapse;}
.dash-table th{font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;padding:10px 14px;text-align:left;background:var(--bg3);border-bottom:1px solid var(--border);}
.dash-table td{font-size:12px;padding:10px 14px;border-bottom:1px solid var(--border);color:var(--text);}
.dash-table tr:last-child td{border-bottom:none;}
.dash-table tr:hover td{background:var(--bg3);}
.dash-table .mono{font-family:var(--mono);font-size:11px;}
.dash-table a{color:var(--accent);font-weight:500;text-decoration:none;}
.dash-table a:hover{text-decoration:underline;}

.dash-bottom{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;}

@media(max-width:1100px){
  .dash-layout{grid-template-columns:1fr;}
  .dash-bottom{grid-template-columns:1fr;}
}
@media(max-width:768px){
  /* Hero: compact padding + smaller fonts */
  .dash-hero{padding:16px 16px;}
  .dash-hero-inner{grid-template-columns:1fr;gap:12px;}
  .dash-hero h1{font-size:10px;margin-bottom:3px;}
  .dash-hero-title{font-size:17px;}
  .dash-hero-sub{font-size:11px;margin-top:4px;}
  .dash-hero-metrics{justify-content:flex-start;gap:16px;flex-wrap:wrap;}
  .dash-hero-metric{text-align:left;}
  .dash-hero-metric .val{font-size:16px;}
  .dash-hero-metric .lbl{font-size:9px;}
  /* Actions: 2 columns on mobile */
  .dash-actions{grid-template-columns:1fr 1fr;gap:8px;}
  .dash-action{padding:10px 12px;gap:8px;}
  .dash-action-ico{width:32px;height:32px;font-size:14px;border-radius:8px;}
  .dash-action-txt{font-size:12px;}
  .dash-action-sub{font-size:10px;}
  /* Flow stages */
  .dash-flow-stages{grid-template-columns:repeat(2,1fr);}
  /* Bottom grid already handled by 1100px rule, ensure on small phones */
  .dash-bottom{grid-template-columns:1fr;}
  .dash-chart{height:200px;}
  .dash-chart.sm{height:160px;}
  /* Rank amounts: prevent overflow */
  .dash-rank-amt{font-size:10px;}
  /* Panel padding tighter */
  .dash-panel{padding:14px;}
}
@media(max-width:480px){
  .dash-hero{padding:14px 14px;}
  .dash-hero-title{font-size:15px;}
  .dash-hero-metric .val{font-size:14px;}
  .dash-hero-metrics{gap:12px;}
  .dash-actions{grid-template-columns:1fr 1fr;gap:6px;}
  .dash-action-sub{display:none;}
  .dash-action-ico{width:28px;height:28px;font-size:13px;}
}
</style>
@endpush

@section('content')
@php
  $stages = ['waiting','designing','approval','printing','fabrication','ready','installed','paid'];
  $stageColors = [
    'waiting' => '#64748b', 'designing' => '#3b82f6', 'approval' => '#f59e0b',
    'printing' => '#8b5cf6', 'fabrication' => '#f97316', 'ready' => '#22c55e',
    'installed' => '#14b8a6', 'paid' => $bmsBrand['primary'] ?? ($bmsSettings['brand_color'] ?? '#b91c1c'),
  ];
  $totalRevenue = $jobs->where('paid', true)->sum('amount');
  $pendingRevenue = $jobs->where('paid', false)->sum('amount');
  $activeJobs = $jobs->whereNotIn('stage', ['installed','paid'])->count();
  $todayEntries = $salesLog->filter(fn ($s) => optional($s->date)->toDateString() === $today)->count();
  $unpaidCount = $jobs->where('paid', false)->count();
  $stageCounts = collect($stages)->mapWithKeys(fn($s) => [$s => $jobs->where('stage', $s)->count()]);
  $totalJobs = max(1, $jobs->count());
  $lowStock = $inventory->filter(fn($i) => $i->qty <= ($i->reorder_level + 1))->take(5);
  $upcoming = $jobs->filter(fn($j) => $j->deadline && $j->deadline > $today)->sortBy('deadline')->take(5);
  $urgentDeadlines = $jobs->filter(fn($j) => $j->deadline && $j->deadline >= $today && $j->deadline <= now()->addDays(3)->toDateString())->count();
  $isLight = ($bmsSettings['theme'] ?? 'dark') === 'light';
  $maxBranchRev = max(1, $branchStats->max('revenue') ?? 1);
  $maxCatJobs = max(1, $jobsByCategory->max() ?? 1);
  $salesTrend = $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100) : ($todaySales > 0 ? 100 : 0);
  $weekTrend = $prevWeekRevenue > 0 ? round((($weekRevenue - $prevWeekRevenue) / $prevWeekRevenue) * 100) : ($weekRevenue > 0 ? 100 : 0);
  $completionRate = $jobs->count() > 0 ? round($jobs->whereIn('stage', ['installed','paid'])->count() / $jobs->count() * 100) : 0;
  $branchLabel = $bmsBranch === 'all' ? 'All Branches' : $bmsBranch;
  $firstName = explode(' ', $bmsUser?->name ?? 'there')[0];
  $showSummaries = $canViewDashboardSummaries ?? false;
@endphp

<div class="dash">

  {{-- Hero banner --}}
  <div class="dash-hero">
    <div class="dash-hero-inner">
      <div>
        <h1>Operations Overview</h1>
        <div class="dash-hero-title">{{ $branchLabel }} · {{ now()->format('D, d M Y') }}</div>
        <div class="dash-hero-sub">Welcome back, {{ $firstName }} — {{ $activeJobs }} jobs in production, {{ $completionRate }}% delivered</div>
      </div>
      @if($showSummaries)
      <div class="dash-hero-metrics">
        <div class="dash-hero-metric">
          <div class="lbl">Today's Sales</div>
          <div class="val">{{ $bmsCurrency }} {{ number_format($todaySales) }}</div>
          <div class="sub">{{ $todayEntries }} entries logged</div>
          @if($todaySales > 0 || $yesterdaySales > 0)
            <span class="chg {{ $salesTrend >= 0 ? 'up' : 'down' }}">{{ $salesTrend >= 0 ? '↑' : '↓' }} {{ abs($salesTrend) }}% vs yesterday</span>
          @endif
        </div>
        <div class="dash-hero-metric">
          <div class="lbl">This Week</div>
          <div class="val">{{ $bmsCurrency }} {{ number_format($weekRevenue) }}</div>
          <div class="sub">7-day sales log</div>
          @if($weekTrend !== 0)
            <span class="chg {{ $weekTrend >= 0 ? 'up' : 'down' }}">{{ $weekTrend >= 0 ? '↑' : '↓' }} {{ abs($weekTrend) }}% vs prior week</span>
          @endif
        </div>
        <div class="dash-hero-metric">
          <div class="lbl">Collected</div>
          <div class="val">{{ $bmsCurrency }} {{ number_format($totalRevenue) }}</div>
          <div class="sub">{{ $bmsCurrency }} {{ number_format($monthRevenue) }} in 30 days</div>
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Quick actions --}}
  <div class="dash-actions">
    <a href="{{ route('bms.jobs.create') }}" class="dash-action">
      <div class="dash-action-ico">➕</div>
      <div><div class="dash-action-txt">New Job</div><div class="dash-action-sub">Create print order</div></div>
    </a>
    <a href="{{ route('bms.saleslog.create') }}" class="dash-action">
      <div class="dash-action-ico green">💵</div>
      <div><div class="dash-action-txt">Log Sale</div><div class="dash-action-sub">Daily sales entry</div></div>
    </a>
    <a href="{{ route('bms.kanban') }}" class="dash-action">
      <div class="dash-action-ico blue">🗂</div>
      <div><div class="dash-action-txt">Kanban</div><div class="dash-action-sub">{{ $activeJobs }} active jobs</div></div>
    </a>
    <a href="{{ route('bms.reports') }}" class="dash-action">
      <div class="dash-action-ico purple">📊</div>
      <div><div class="dash-action-txt">Reports</div><div class="dash-action-sub">Analytics & PDFs</div></div>
    </a>
  </div>

  {{-- Main layout --}}
  <div class="dash-layout">
    <div>
      {{-- Revenue chart --}}
      <div class="dash-panel">
        @if($showSummaries)
        <div class="dash-panel-hd">
          <div>
            <div class="dash-panel-title">Revenue Pulse</div>
            <div class="dash-panel-desc">30-day daily sales from log</div>
          </div>
          <span class="dash-tag">{{ $bmsCurrency }} {{ number_format($monthRevenue) }} total</span>
        </div>
        <div class="dash-chart">
          <canvas id="chartRevenue"></canvas>
        </div>
        @endif

        {{-- Production flow --}}
        <div class="dash-flow"@unless($showSummaries) style="margin-top:0;padding-top:0;border-top:none;"@endunless>
          <div class="dash-panel-hd" style="margin-bottom:12px;">
            <div>
              <div class="dash-panel-title">Production Flow</div>
              <div class="dash-panel-desc">{{ $jobs->count() }} jobs across pipeline stages</div>
            </div>
            <a href="{{ route('bms.kanban') }}" class="btn btn-secondary btn-sm">Open Board</a>
          </div>
          <div class="dash-flow-bar">
            @foreach($stages as $stage)
              @php $count = $stageCounts[$stage]; @endphp
              @if($count > 0)
                <div class="dash-flow-seg" style="flex:{{ $count }};background:{{ $stageColors[$stage] }};" title="{{ ucfirst($stage) }}: {{ $count }}">{{ $count > 2 ? $count : '' }}</div>
              @endif
            @endforeach
          </div>
          <div class="dash-flow-stages">
            @foreach(array_slice($stages, 0, 4) as $stage)
              <div class="dash-flow-stage">
                <div class="cnt" style="color:{{ $stageColors[$stage] }}">{{ $stageCounts[$stage] }}</div>
                <div class="name">{{ $stage }}</div>
              </div>
            @endforeach
          </div>
          <div class="dash-flow-stages" style="margin-top:8px;">
            @foreach(array_slice($stages, 4) as $stage)
              <div class="dash-flow-stage">
                <div class="cnt" style="color:{{ $stageColors[$stage] }}">{{ $stageCounts[$stage] }}</div>
                <div class="name">{{ $stage }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Category breakdown --}}
      <div class="dash-panel" style="margin-top:14px;">
        <div class="dash-panel-hd">
          <div>
            <div class="dash-panel-title">Category Mix</div>
            <div class="dash-panel-desc">Job volume by service type</div>
          </div>
        </div>
        <div class="dash-cat-list">
          @forelse($jobsByCategory as $cat => $count)
            <div class="dash-cat-row">
              <div class="dash-cat-name">{{ $cat ?: 'Uncategorised' }}</div>
              <div class="dash-cat-track">
                <div class="dash-cat-fill" style="width:{{ round($count / $maxCatJobs * 100) }}%;opacity:{{ .5 + ($loop->index % 5) * .1 }};"></div>
              </div>
              <div class="dash-cat-val">{{ $count }}</div>
            </div>
          @empty
            <div class="empty-state" style="padding:24px;"><p>No category data</p></div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Sidebar --}}
    <div class="dash-sidebar">
      @if($showSummaries && $pendingRevenue > 0)
        <div class="dash-alert danger">
          <div class="dash-alert-ico">💳</div>
          <div class="dash-alert-body">
            <div class="dash-alert-title">Outstanding Payments</div>
            <div class="dash-alert-val red">{{ $bmsCurrency }} {{ number_format($pendingRevenue) }}</div>
            <div class="dash-alert-sub">{{ $unpaidCount }} job{{ $unpaidCount !== 1 ? 's' : '' }} awaiting payment</div>
          </div>
        </div>
      @endif

      @if($lowStock->count() > 0)
        <div class="dash-alert warn">
          <div class="dash-alert-ico">📦</div>
          <div class="dash-alert-body">
            <div class="dash-alert-title">Low Stock</div>
            <div class="dash-alert-val yellow">{{ $lowStock->count() }} item{{ $lowStock->count() !== 1 ? 's' : '' }}</div>
            <div class="dash-alert-sub">Below reorder level</div>
          </div>
        </div>
      @endif

      @if($urgentDeadlines > 0)
        <div class="dash-alert warn">
          <div class="dash-alert-ico">⏰</div>
          <div class="dash-alert-body">
            <div class="dash-alert-title">Due Soon</div>
            <div class="dash-alert-val yellow">{{ $urgentDeadlines }} deadline{{ $urgentDeadlines !== 1 ? 's' : '' }}</div>
            <div class="dash-alert-sub">Within next 3 days</div>
          </div>
        </div>
      @endif

      @if($showSummaries)
      <div class="dash-panel">
        <div class="dash-panel-hd">
          <div>
            <div class="dash-panel-title">Branch Leaderboard</div>
            <div class="dash-panel-desc">Paid revenue ranking</div>
          </div>
        </div>
        <div class="dash-rank">
          @foreach($branchStats->sortByDesc('revenue')->values() as $i => $branch)
            <div class="dash-rank-row">
              <div class="dash-rank-num {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</div>
              <div class="dash-rank-info">
                <div class="dash-rank-name">{{ $branch['name'] }}</div>
                <div class="dash-rank-bar">
                  <div class="dash-rank-fill" style="width:{{ round($branch['revenue'] / $maxBranchRev * 100) }}%;"></div>
                </div>
              </div>
              <div class="dash-rank-amt">{{ $bmsCurrency }} {{ number_format($branch['revenue']) }}</div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      <div class="dash-panel">
        <div class="dash-panel-hd">
          <div>
            <div class="dash-panel-title">Stage Breakdown</div>
            <div class="dash-panel-desc">Jobs per stage</div>
          </div>
        </div>
        <div class="dash-chart sm">
          <canvas id="chartStage"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Bottom row --}}
  <div class="dash-bottom">
    <div class="dash-panel">
      <div class="dash-panel-hd">
        <div>
          <div class="dash-panel-title">Upcoming Deadlines</div>
          <div class="dash-panel-desc">Next jobs due</div>
        </div>
        <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary btn-sm">All Jobs</a>
      </div>
      @forelse($upcoming as $job)
        @php $daysLeft = \Carbon\Carbon::parse($job->deadline)->diffInDays($today); @endphp
        <div class="activity-item">
          <div style="flex:1;min-width:0;">
            <div class="mono text-muted" style="font-size:10px;">{{ $job->id }}</div>
            <div style="font-size:12px;font-weight:500;">{{ Str::limit($job->title, 38) }}</div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:11px;font-weight:600;color:var(--yellow);">{{ \Carbon\Carbon::parse($job->deadline)->format('d M') }}</div>
            <div style="font-size:10px;color:var(--text3);">{{ $daysLeft === 0 ? 'Today' : $daysLeft . 'd left' }}</div>
          </div>
        </div>
      @empty
        <div class="empty-state" style="padding:24px;"><div class="empty-icon">📅</div><p>No upcoming deadlines</p></div>
      @endforelse
    </div>

    <div class="dash-panel">
      <div class="dash-panel-hd">
        <div>
          <div class="dash-panel-title">Inventory Alerts</div>
          <div class="dash-panel-desc">Items at or below reorder level</div>
        </div>
        <a href="{{ route('bms.inventory.index') }}" class="btn btn-secondary btn-sm">Manage</a>
      </div>
      @forelse($lowStock as $item)
        <div class="activity-item">
          <div class="activity-icon" style="background:var(--red-dim);color:var(--red);">⚠</div>
          <div style="flex:1;font-size:12px;">{{ $item->name }}</div>
          <span class="badge badge-red">{{ $item->qty }} {{ $item->unit }}</span>
        </div>
      @empty
        <div class="empty-state" style="padding:24px;"><div class="empty-icon">✓</div><p>Stock levels healthy</p></div>
      @endforelse
    </div>
  </div>

  {{-- Recent jobs table --}}
  <div class="dash-panel" style="margin-top:16px;">
    <div class="dash-panel-hd">
      <div>
        <div class="dash-panel-title">Recent Jobs</div>
        <div class="dash-panel-desc">Latest updated print orders</div>
      </div>
      <a href="{{ route('bms.jobs.create') }}" class="btn btn-primary btn-sm">+ New Job</a>
    </div>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Job ID</th>
            <th>Title</th>
            <th>Client</th>
            <th>Branch</th>
            @if($showSummaries)<th>Amount</th>@endif
            <th>Stage</th>
          </tr>
        </thead>
        <tbody>
          @forelse($jobs->sortByDesc('updated_at')->take(8) as $job)
            <tr>
              <td class="mono"><a href="{{ route('bms.jobs.show', $job) }}">{{ $job->id }}</a></td>
              <td>{{ Str::limit($job->title, 35) }}</td>
              <td class="text-muted">{{ $clients->get($job->client_id)?->name ?? '—' }}</td>
              <td class="text-muted">{{ $job->branch ?? '—' }}</td>
              @if($showSummaries)<td class="mono">{{ $bmsCurrency }} {{ number_format($job->amount) }}</td>@endif
              <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
            </tr>
          @empty
            <tr><td colspan="{{ $showSummaries ? 6 : 5 }}" style="text-align:center;color:var(--text3);padding:32px;">No jobs yet — create your first order</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
  const light = {{ $isLight ? 'true' : 'false' }};
  const text2 = light ? '#6b7280' : '#8892a4';
  const text3 = light ? '#9ca3af' : '#5a6475';
  const border = light ? '#e5e7eb' : '#2a2f3a';
  const gridCol = light ? 'rgba(0,0,0,.04)' : 'rgba(255,255,255,.04)';
  const accent = @json($bmsBrand['primary'] ?? '#b91c1c');
  const accentRgb = @json($bmsBrand['rgb'] ?? '185, 28, 28');

  const stageColors = {
    waiting:'#64748b', designing:'#3b82f6', approval:'#f59e0b',
    printing:'#8b5cf6', fabrication:'#f97316', ready:'#22c55e',
    installed:'#14b8a6', paid: accent
  };

  Chart.defaults.font.family = "'IBM Plex Sans',sans-serif";
  Chart.defaults.font.size = 11;
  Chart.defaults.color = text2;
  Chart.defaults.animation.duration = 900;

  const tooltip = {
    backgroundColor: light ? '#fff' : '#1a1d24',
    titleColor: light ? '#1a1d23' : '#e8eaf0',
    bodyColor: light ? '#5a6070' : '#9ba3b8',
    borderColor: border, borderWidth: 1, padding: 10, cornerRadius: 8,
  };

  // Revenue — smooth area chart
  @if($showSummaries)
  const revCanvas = document.getElementById('chartRevenue');
  const revGrad = revCanvas.getContext('2d').createLinearGradient(0, 0, 0, 280);
  revGrad.addColorStop(0, light ? `rgba(${accentRgb}, 0.2)` : `rgba(${accentRgb}, 0.35)`);
  revGrad.addColorStop(1, `rgba(${accentRgb}, 0)`);

  new Chart(revCanvas, {
    type: 'line',
    data: {
      labels: @json($revenueByDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
      datasets: [{
        label: 'Revenue',
        data: @json($revenueByDay->values()),
        borderColor: accent,
        backgroundColor: revGrad,
        borderWidth: 2,
        fill: true,
        tension: 0.45,
        pointRadius: 0,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: accent,
        pointHoverBorderWidth: 2,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: { ...tooltip, callbacks: { label: c => ' KSh ' + Number(c.raw).toLocaleString() } }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: text3, maxTicksLimit: 7 }, border: { display: false } },
        y: {
          grid: { color: gridCol },
          ticks: { color: text3, callback: v => v >= 1000 ? (v/1000).toFixed(0)+'k' : v },
          border: { display: false }
        }
      }
    }
  });
  @endif

  // Stage — polar area (distinct from doughnut)
  const stageLabels = @json(array_values($stages));
  const stageCounts = @json(collect($stages)->map(fn($s) => (int)($stageCounts[$s] ?? 0))->values());

  new Chart(document.getElementById('chartStage'), {
    type: 'polarArea',
    data: {
      labels: stageLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
      datasets: [{
        data: stageCounts,
        backgroundColor: stageLabels.map(s => (stageColors[s] || '#64748b') + (light ? '99' : 'bb')),
        borderColor: light ? '#fff' : '#1a1d24',
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 8, padding: 8, color: text2, font: { size: 9 }, usePointStyle: true }
        },
        tooltip: {
          ...tooltip,
          callbacks: {
            label: c => {
              const t = c.dataset.data.reduce((a,b)=>a+b,0);
              return ` ${c.label}: ${c.raw} (${t ? Math.round(c.raw/t*100) : 0}%)`;
            }
          }
        }
      },
      scales: {
        r: {
          grid: { color: gridCol },
          ticks: { display: false },
          pointLabels: { display: false }
        }
      }
    }
  });
})();
</script>
@endpush
