@extends('layouts.bms')

@section('content')
@php
  $stages = ['waiting','designing','approval','printing','fabrication','ready','installed','paid'];
  $stageColors = [
    'waiting' => '#64748b', 'designing' => '#3b82f6', 'approval' => '#f59e0b',
    'printing' => '#8b5cf6', 'fabrication' => '#f97316', 'ready' => '#22c55e',
    'installed' => '#14b8a6', 'paid' => '#b91c1c',
  ];
  $totalRevenue = $jobs->where('paid', true)->sum('amount');
  $pendingRevenue = $jobs->where('paid', false)->sum('amount');
  $activeJobs = $jobs->whereNotIn('stage', ['installed','paid'])->count();
  $todayEntries = $salesLog->where('date', $today)->count();
  $unpaidCount = $jobs->where('paid', false)->count();
  $stageCounts = collect($stages)->mapWithKeys(fn($s) => [$s => $jobs->where('stage', $s)->count()]);
  $totalJobs = max(1, $jobs->count());
  $lowStock = $inventory->filter(fn($i) => $i->qty <= ($i->reorder_level + 1))->take(5);
  $upcoming = $jobs->filter(fn($j) => $j->deadline && $j->deadline > $today)->sortBy('deadline')->take(5);
  $isLight = ($bmsSettings['theme'] ?? 'dark') === 'light';

  $salesTrend = $yesterdaySales > 0
    ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100)
    : ($todaySales > 0 ? 100 : 0);
  $weekTrend = $prevWeekRevenue > 0
    ? round((($weekRevenue - $prevWeekRevenue) / $prevWeekRevenue) * 100)
    : ($weekRevenue > 0 ? 100 : 0);
  $completionRate = $jobs->count() > 0
    ? round($jobs->whereIn('stage', ['installed','paid'])->count() / $jobs->count() * 100)
    : 0;
@endphp

{{-- Header --}}
<div class="dashboard-header">
  <div>
    <div class="dashboard-greeting">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $bmsUser?->name ?? 'there')[0] }}</div>
    <div class="dashboard-meta">{{ now()->format('l, d M Y') }} · {{ $bmsBranch === 'all' ? 'All branches' : $bmsBranch }}</div>
  </div>
  <div class="dashboard-actions">
    <a href="{{ route('bms.reports') }}" class="btn btn-secondary btn-sm">Reports</a>
    <a href="{{ route('bms.kanban') }}" class="btn btn-primary btn-sm">Open Kanban</a>
  </div>
</div>

{{-- KPI row --}}
<div class="grid-4 mb-2">
  <div class="stat-card" style="--stat-accent:var(--accent);--stat-icon-bg:var(--accent-dim);">
    <div class="stat-card-head">
      <div class="stat-label">Active Jobs</div>
      <div class="stat-icon">📋</div>
    </div>
    <div class="stat-value accent">{{ $activeJobs }}</div>
    <div class="stat-sub">{{ $jobs->count() }} total · {{ $completionRate }}% complete</div>
  </div>

  <div class="stat-card" style="--stat-accent:var(--green);--stat-icon-bg:var(--green-dim);">
    <div class="stat-card-head">
      <div class="stat-label">Revenue (Paid)</div>
      <div class="stat-icon">💰</div>
    </div>
    <div class="stat-value green">KSh {{ number_format($totalRevenue) }}</div>
    <div class="stat-sub">KSh {{ number_format($monthRevenue) }} last 30 days</div>
    @if($weekTrend !== 0)
      <div class="stat-trend {{ $weekTrend >= 0 ? 'up' : 'down' }}">
        {{ $weekTrend >= 0 ? '↑' : '↓' }} {{ abs($weekTrend) }}% vs last week
      </div>
    @endif
  </div>

  <div class="stat-card" style="--stat-accent:var(--red);--stat-icon-bg:var(--red-dim);">
    <div class="stat-card-head">
      <div class="stat-label">Pending Payment</div>
      <div class="stat-icon">⏳</div>
    </div>
    <div class="stat-value red">KSh {{ number_format($pendingRevenue) }}</div>
    <div class="stat-sub">{{ $unpaidCount }} unpaid job{{ $unpaidCount !== 1 ? 's' : '' }}</div>
  </div>

  <div class="stat-card" style="--stat-accent:var(--blue);--stat-icon-bg:var(--blue-dim);">
    <div class="stat-card-head">
      <div class="stat-label">Today's Sales</div>
      <div class="stat-icon">📈</div>
    </div>
    <div class="stat-value blue">KSh {{ number_format($todaySales) }}</div>
    <div class="stat-sub">{{ $todayEntries }} log entr{{ $todayEntries !== 1 ? 'ies' : 'y' }}</div>
    @if($todaySales > 0 || $yesterdaySales > 0)
      <div class="stat-trend {{ $salesTrend >= 0 ? 'up' : 'down' }}">
        {{ $salesTrend >= 0 ? '↑' : '↓' }} {{ abs($salesTrend) }}% vs yesterday
      </div>
    @endif
  </div>
</div>

{{-- Charts row 1 --}}
<div class="grid-2 mb-2">
  <div class="card chart-card">
    <div class="card-header">
      <div>
        <div class="card-title">30-Day Revenue Trend</div>
        <div style="font-size:11px;color:var(--text3);margin-top:3px;">Daily sales log totals</div>
      </div>
      <span class="chart-badge">KSh {{ number_format($monthRevenue) }}</span>
    </div>
    <div class="chart-wrap">
      <canvas id="chartRevenue"></canvas>
    </div>
  </div>

  <div class="card chart-card">
    <div class="card-header">
      <div>
        <div class="card-title">Jobs by Stage</div>
        <div style="font-size:11px;color:var(--text3);margin-top:3px;">Pipeline distribution</div>
      </div>
      <span class="chart-badge">{{ $jobs->count() }} jobs</span>
    </div>
    <div class="chart-wrap donut">
      <canvas id="chartStage"></canvas>
    </div>
  </div>
</div>

{{-- Charts row 2 --}}
<div class="grid-2 mb-2">
  <div class="card chart-card">
    <div class="card-header">
      <div>
        <div class="card-title">Jobs by Category</div>
        <div style="font-size:11px;color:var(--text3);margin-top:3px;">Volume & paid revenue</div>
      </div>
      <span class="chart-badge">Top {{ $jobsByCategory->count() }}</span>
    </div>
    <div class="chart-wrap sm">
      <canvas id="chartCategory"></canvas>
    </div>
  </div>

  <div class="card chart-card">
    <div class="card-header">
      <div>
        <div class="card-title">Revenue by Branch</div>
        <div style="font-size:11px;color:var(--text3);margin-top:3px;">Paid jobs only</div>
      </div>
      <span class="chart-badge">{{ $branchStats->count() }} branches</span>
    </div>
    <div class="chart-wrap sm">
      <canvas id="chartBranch"></canvas>
    </div>
  </div>
</div>

{{-- Pipeline + Recent Activity --}}
<div class="grid-2 mb-2">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Job Pipeline</div>
      <a href="{{ route('bms.kanban') }}" class="btn btn-secondary btn-sm">View Kanban</a>
    </div>
    @foreach($stages as $stage)
      @php
        $count = $stageCounts[$stage];
        $pct = $count > 0 ? max(8, round($count / $totalJobs * 100)) : 0;
        $color = $stageColors[$stage] ?? '#64748b';
      @endphp
      <div class="pipeline-row">
        <div class="pipeline-label">{{ $stage }}</div>
        <div class="pipeline-track">
          <div class="pipeline-fill" style="width:{{ $pct }}%;background:{{ $color }};">{{ $count }}</div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Recent Activity</div>
      <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary btn-sm">All Jobs</a>
    </div>
    <div class="widget-list">
      @forelse($jobs->sortByDesc('updated_at')->take(6) as $job)
        <div class="activity-item">
          <div class="activity-icon" style="background:var(--accent-dim);color:var(--accent)">📋</div>
          <div class="activity-text">
            <span class="mono text-accent" style="font-size:11px;">{{ $job->id }}</span>
            — {{ Str::limit($job->title, 40) }}
            <span class="text-muted"> ({{ $job->client?->name ?? '—' }})</span>
          </div>
          <span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span>
        </div>
      @empty
        <div class="empty-state"><div class="empty-icon">📋</div><p>No jobs yet</p></div>
      @endforelse
    </div>
  </div>
</div>

{{-- Bottom row --}}
<div class="grid-3">
  <div class="card">
    <div class="card-header"><div class="card-title">Branch Summary</div></div>
    <div class="widget-list">
      @foreach($branchStats as $branch)
        <div class="activity-item">
          <div class="branch-dot"></div>
          <div style="flex:1;font-size:13px;font-weight:500;">{{ $branch['name'] }}</div>
          <div style="font-size:12px;color:var(--text2)">{{ $branch['jobs'] }} jobs</div>
          <div class="mono text-accent" style="font-size:12px;margin-left:12px;">KSh {{ number_format($branch['revenue']) }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Inventory Alerts</div>
      <a href="{{ route('bms.inventory.index') }}" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="widget-list">
      @forelse($lowStock as $item)
        <div class="activity-item">
          <div class="activity-icon" style="background:var(--red-dim);color:var(--red)">⚠</div>
          <div style="flex:1;font-size:13px;">{{ $item->name }}</div>
          <span class="badge badge-red">{{ $item->qty }} {{ $item->unit }}</span>
        </div>
      @empty
        <div class="empty-state"><div class="empty-icon">✓</div><p>All stock levels OK</p></div>
      @endforelse
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Upcoming Deadlines</div></div>
    <div class="widget-list">
      @forelse($upcoming as $job)
        @php $daysLeft = \Carbon\Carbon::parse($job->deadline)->diffInDays($today); @endphp
        <div class="activity-item">
          <div style="flex:1;">
            <div class="mono text-muted" style="font-size:11px;">{{ $job->id }}</div>
            <div style="font-size:13px;">{{ Str::limit($job->title, 35) }}</div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:11px;color:var(--yellow);font-weight:600;">{{ \Carbon\Carbon::parse($job->deadline)->format('d M') }}</div>
            <div style="font-size:10px;color:var(--text3);">{{ $daysLeft === 0 ? 'Today' : $daysLeft . 'd left' }}</div>
          </div>
        </div>
      @empty
        <div class="empty-state"><div class="empty-icon">📅</div><p>No upcoming deadlines</p></div>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
  const light = {{ $isLight ? 'true' : 'false' }};

  const text2   = light ? '#6b7280' : '#8892a4';
  const text3   = light ? '#9ca3af' : '#5a6475';
  const border  = light ? '#e5e7eb' : '#2a2f3a';
  const gridCol = light ? 'rgba(0,0,0,.05)' : 'rgba(255,255,255,.04)';
  const accent  = '#b91c1c';
  const green   = '#22c55e';
  const blue    = '#3b82f6';
  const yellow  = '#f59e0b';
  const purple  = '#8b5cf6';
  const pink    = '#ec4899';
  const teal    = '#14b8a6';
  const orange  = '#f97316';

  const stageColors = {
    waiting:'#64748b', designing:'#3b82f6', approval:'#f59e0b',
    printing:'#8b5cf6', fabrication:'#f97316', ready:'#22c55e',
    installed:'#14b8a6', paid:'#b91c1c'
  };

  Chart.defaults.font.family = "'IBM Plex Sans','Segoe UI',sans-serif";
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = text2;
  Chart.defaults.animation.duration = 800;
  Chart.defaults.animation.easing = 'easeOutQuart';

  const baseGrid = { color: gridCol, drawBorder: false };
  const baseTick = { color: text3, font: { size: 10 } };

  const tooltipDefaults = {
    backgroundColor: light ? '#ffffff' : '#1a1d24',
    titleColor: light ? '#1a1d23' : '#e8eaf0',
    bodyColor: light ? '#5a6070' : '#9ba3b8',
    borderColor: border,
    borderWidth: 1,
    padding: 10,
    cornerRadius: 6,
    boxPadding: 4,
  };

  // Doughnut center label plugin
  const centerText = {
    id: 'centerText',
    afterDraw(chart) {
      if (chart.config.type !== 'doughnut') return;
      const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
      const { ctx } = chart;
      const meta = chart.getDatasetMeta(0);
      if (!meta.data.length) return;
      const { x, y } = meta.data[0];
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = light ? '#1a1d23' : '#e8eaf0';
      ctx.font = '700 22px IBM Plex Sans, sans-serif';
      ctx.fillText(total, x, y - 6);
      ctx.fillStyle = text3;
      ctx.font = '500 10px IBM Plex Sans, sans-serif';
      ctx.fillText('TOTAL JOBS', x, y + 12);
      ctx.restore();
    }
  };
  Chart.register(centerText);

  // 1. Revenue trend
  const revCanvas = document.getElementById('chartRevenue');
  const revLabels = @json($revenueByDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
  const revData   = @json($revenueByDay->values());

  const revGradient = revCanvas.getContext('2d').createLinearGradient(0, 0, 0, 260);
  revGradient.addColorStop(0, light ? 'rgba(185,28,28,.18)' : 'rgba(185,28,28,.28)');
  revGradient.addColorStop(1, light ? 'rgba(185,28,28,0)' : 'rgba(185,28,28,0)');

  new Chart(revCanvas, {
    type: 'line',
    data: {
      labels: revLabels,
      datasets: [{
        label: 'Revenue (KSh)',
        data: revData,
        borderColor: accent,
        backgroundColor: revGradient,
        borderWidth: 2.5,
        pointRadius: 0,
        pointHoverRadius: 5,
        pointBackgroundColor: accent,
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: accent,
        pointHoverBorderWidth: 2,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          ...tooltipDefaults,
          callbacks: { label: ctx => ' KSh ' + Number(ctx.raw).toLocaleString() }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { ...baseTick, maxTicksLimit: 8, maxRotation: 0 },
          border: { display: false },
        },
        y: {
          grid: baseGrid,
          ticks: {
            ...baseTick,
            callback: v => v >= 1000 ? 'KSh ' + (v/1000).toFixed(0) + 'k' : 'KSh ' + v
          },
          border: { display: false },
        }
      }
    }
  });

  // 2. Stage doughnut
  const stageLabels = @json(array_values($stages));
  const stageCounts = @json(collect($stages)->map(fn($s) => (int)($stageCounts[$s] ?? 0))->values());

  new Chart(document.getElementById('chartStage'), {
    type: 'doughnut',
    data: {
      labels: stageLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
      datasets: [{
        data: stageCounts,
        backgroundColor: stageLabels.map(s => stageColors[s] ?? '#64748b'),
        borderColor: light ? '#ffffff' : '#1a1d24',
        borderWidth: 3,
        hoverOffset: 10,
        hoverBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      layout: { padding: { right: 8 } },
      plugins: {
        legend: {
          position: 'right',
          labels: {
            boxWidth: 10,
            boxHeight: 10,
            padding: 12,
            color: text2,
            font: { size: 10, weight: '500' },
            usePointStyle: true,
            pointStyle: 'circle',
          }
        },
        tooltip: {
          ...tooltipDefaults,
          callbacks: {
            label: ctx => {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const pct = total ? Math.round(ctx.raw / total * 100) : 0;
              return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
            }
          }
        }
      }
    }
  });

  // 3. Category — horizontal bar + revenue line
  const catLabels  = @json($jobsByCategory->keys()->map(fn($k) => $k ?: 'Uncategorised'));
  const catCounts  = @json($jobsByCategory->values());
  const catRevenue = @json($revenueByCategory->values());
  const catColors  = [accent, blue, green, yellow, purple, pink, teal, orange];

  new Chart(document.getElementById('chartCategory'), {
    type: 'bar',
    data: {
      labels: catLabels,
      datasets: [
        {
          label: 'Jobs',
          data: catCounts,
          backgroundColor: catLabels.map((_, i) => catColors[i % catColors.length] + (light ? '99' : 'bb')),
          borderColor: catLabels.map((_, i) => catColors[i % catColors.length]),
          borderWidth: 0,
          borderRadius: 4,
          barThickness: 14,
          xAxisID: 'xJobs',
        },
        {
          label: 'Revenue (KSh)',
          data: catRevenue,
          type: 'line',
          borderColor: yellow,
          backgroundColor: yellow + '22',
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: yellow,
          pointBorderColor: light ? '#fff' : '#1a1d24',
          pointBorderWidth: 2,
          xAxisID: 'xRev',
          tension: 0.35,
          fill: false,
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: { color: text2, boxWidth: 10, padding: 14, font: { size: 10 }, usePointStyle: true }
        },
        tooltip: {
          ...tooltipDefaults,
          callbacks: {
            label: ctx => ctx.datasetIndex === 1
              ? ' KSh ' + Number(ctx.raw).toLocaleString()
              : ' ' + ctx.raw + ' jobs'
          }
        }
      },
      scales: {
        y: {
          grid: { display: false },
          ticks: { ...baseTick, font: { size: 10, weight: '500' } },
          border: { display: false },
        },
        xJobs: {
          type: 'linear', position: 'bottom',
          grid: baseGrid,
          ticks: { ...baseTick, stepSize: 1, precision: 0 },
          border: { display: false },
        },
        xRev: {
          type: 'linear', position: 'top',
          grid: { display: false },
          ticks: {
            ...baseTick,
            callback: v => v >= 1000 ? (v/1000).toFixed(0) + 'k' : v
          },
          border: { display: false },
        }
      }
    }
  });

  // 4. Branch revenue
  const branchLabels  = @json($branchStats->pluck('name'));
  const branchRevenue = @json($branchStats->pluck('revenue'));
  const branchJobs    = @json($branchStats->pluck('jobs'));

  const branchCanvas = document.getElementById('chartBranch');
  const branchGrad = branchCanvas.getContext('2d').createLinearGradient(0, 0, 0, 240);
  branchGrad.addColorStop(0, light ? 'rgba(185,28,28,.85)' : 'rgba(185,28,28,.9)');
  branchGrad.addColorStop(1, light ? 'rgba(185,28,28,.45)' : 'rgba(185,28,28,.55)');

  new Chart(branchCanvas, {
    type: 'bar',
    data: {
      labels: branchLabels,
      datasets: [
        {
          label: 'Revenue (KSh)',
          data: branchRevenue,
          backgroundColor: branchGrad,
          borderColor: accent,
          borderWidth: 0,
          borderRadius: 6,
          borderSkipped: false,
          yAxisID: 'yRev',
        },
        {
          label: 'Jobs',
          data: branchJobs,
          type: 'line',
          borderColor: blue,
          backgroundColor: blue + '18',
          borderWidth: 2.5,
          pointRadius: 4,
          pointBackgroundColor: blue,
          pointBorderColor: light ? '#fff' : '#1a1d24',
          pointBorderWidth: 2,
          yAxisID: 'yJobs',
          tension: 0.35,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          labels: { color: text2, boxWidth: 10, padding: 14, font: { size: 10 }, usePointStyle: true }
        },
        tooltip: {
          ...tooltipDefaults,
          callbacks: {
            label: ctx => ctx.datasetIndex === 0
              ? ' KSh ' + Number(ctx.raw).toLocaleString()
              : ' ' + ctx.raw + ' jobs'
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { ...baseTick, font: { size: 10, weight: '500' } },
          border: { display: false },
        },
        yRev: {
          type: 'linear', position: 'left',
          grid: baseGrid,
          ticks: {
            ...baseTick,
            callback: v => v >= 1000 ? (v/1000).toFixed(0) + 'k' : v
          },
          border: { display: false },
        },
        yJobs: {
          type: 'linear', position: 'right',
          grid: { display: false },
          ticks: { ...baseTick, stepSize: 1, precision: 0 },
          border: { display: false },
        }
      }
    }
  });
})();
</script>
@endpush
