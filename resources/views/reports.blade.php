@extends('layouts.bms')

@section('content')
@php
  $reportTypes = [
    'sales'   => 'Sales Summary',
    'jobs'    => 'Job Status',
    'finance' => 'Financial Overview',
    'vat'     => 'VAT Report',
  ];

  // Group consecutive 'bars' sections into side-by-side pairs
  $grouped = [];
  $pending = null;
  foreach ($sections as $s) {
    if ($s['type'] === 'bars') {
      if ($pending) {
        $grouped[] = ['pair', $pending, $s];
        $pending = null;
      } else {
        $pending = $s;
      }
    } else {
      if ($pending) { $grouped[] = ['single', $pending]; $pending = null; }
      $grouped[] = ['single', $s];
    }
  }
  if ($pending) $grouped[] = ['single', $pending];
@endphp

<div class="page-header">
  <div>
    <div class="page-title">Reports Center</div>
    <div class="page-subtitle">{{ $reportTypes[$type] ?? 'Report' }} &mdash; {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</div>
  </div>
  <a href="{{ route('bms.reports.pdf', ['type' => $type, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
     class="btn btn-primary" target="_blank">&#8659; View PDF</a>
</div>

{{-- Report Type Tabs --}}
<div style="display:flex;gap:2px;margin-bottom:20px;border-bottom:2px solid var(--border);">
  @foreach($reportTypes as $key => $label)
  <a href="{{ route('bms.reports', array_filter(['type' => $key, 'from' => request('from'), 'to' => request('to'), 'branch' => request('branch')])) }}"
     style="padding:9px 18px;border-radius:6px 6px 0 0;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:-2px;
            {{ $type === $key ? 'background:var(--accent);color:#fff;' : 'color:var(--text2);' }}">
    {{ $label }}
  </a>
  @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('bms.reports') }}"
      style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
  <input type="hidden" name="type" value="{{ $type }}">
  <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="fld" style="width:148px;">
  <span style="color:var(--text3);font-size:12px;">to</span>
  <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="fld" style="width:148px;">
  @if(count($branches) > 1)
  <select name="branch" class="fld" style="width:160px;">
    <option value="">All Branches</option>
    @foreach($branches as $b)
    <option value="{{ $b }}" {{ request('branch') === $b ? 'selected' : '' }}>{{ $b }}</option>
    @endforeach
  </select>
  @endif
  <button type="submit" class="btn btn-secondary">Apply</button>
  <a href="{{ route('bms.reports', ['type' => $type]) }}"
     style="font-size:12px;color:var(--text3);text-decoration:none;padding:6px 10px;">Reset</a>
</form>

{{-- KPI Cards --}}
<div class="stats-grid" style="margin-bottom:24px;">
  @foreach($kpis as $kpi)
  <div class="stat-card">
    <div class="stat-label">{{ $kpi['label'] }}</div>
    <div class="stat-value" @isset($kpi['color'])style="color:{{ $kpi['color'] }};"@endisset>{{ $kpi['value'] }}</div>
    @isset($kpi['sub'])<div class="stat-sub">{{ $kpi['sub'] }}</div>@endisset
  </div>
  @endforeach
</div>

{{-- Sections --}}
@foreach($grouped as $group)

  @if($group[0] === 'pair')
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
    @foreach([$group[1], $group[2]] as $section)
    <div class="card">
      <div class="card-title" style="margin-bottom:16px;">{{ $section['title'] }}</div>
      @if(count($section['rows']) > 0)
        @php $maxVal = max(array_column($section['rows'], 'value') ?: [1]) ?: 1; @endphp
        @foreach($section['rows'] as $row)
        <div style="margin-bottom:10px;">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
            <span>{{ $row['label'] }}</span>
            <span style="color:var(--text3);">{{ $row['count'] ?? $row['value'] }}</span>
          </div>
          <div style="background:var(--bg3);border-radius:4px;height:6px;">
            <div style="background:{{ $row['color'] ?? 'var(--accent)' }};border-radius:4px;height:6px;
                        width:{{ max(round($row['value'] / $maxVal * 100), 2) }}%;transition:width .3s;"></div>
          </div>
        </div>
        @endforeach
      @else
        <p style="color:var(--text3);font-size:13px;">No data for this period.</p>
      @endif
    </div>
    @endforeach
  </div>

  @else
  @php $section = $group[1]; @endphp
  <div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:16px;">{{ $section['title'] }}</div>

    @if($section['type'] === 'table')
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              @foreach($section['columns'] as $col)
              <th @if(!empty($col['right']))class="text-right"@endif>{{ $col['label'] }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @forelse($section['rows'] as $row)
            <tr>
              @foreach($section['columns'] as $col)
              <td @class(['text-right mono' => !empty($col['right'])])
                  @if(!empty($col['bold']))style="font-weight:600;"@endif>
                {{ $row[$col['key']] ?? '—' }}
              </td>
              @endforeach
            </tr>
            @empty
            <tr>
              <td colspan="{{ count($section['columns']) }}">
                <div class="empty-state"><p>No data for this period.</p></div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    @elseif($section['type'] === 'bars')
      @if(count($section['rows']) > 0)
        @php $maxVal = max(array_column($section['rows'], 'value') ?: [1]) ?: 1; @endphp
        @foreach($section['rows'] as $row)
        <div style="margin-bottom:10px;">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
            <span>{{ $row['label'] }}</span>
            <span style="color:var(--text3);">{{ $row['count'] ?? $row['value'] }}</span>
          </div>
          <div style="background:var(--bg3);border-radius:4px;height:6px;">
            <div style="background:{{ $row['color'] ?? 'var(--accent)' }};border-radius:4px;height:6px;
                        width:{{ max(round($row['value'] / $maxVal * 100), 2) }}%;transition:width .3s;"></div>
          </div>
        </div>
        @endforeach
      @else
        <p style="color:var(--text3);font-size:13px;">No data for this period.</p>
      @endif

    @elseif($section['type'] === 'monthly')
      @if(count($section['rows']) > 0)
        @php $maxVal = max(array_column($section['rows'], 'value') ?: [1]) ?: 1; @endphp
        <div style="display:flex;align-items:flex-end;gap:8px;height:130px;">
          @foreach($section['rows'] as $row)
          @php $h = max(round($row['value'] / $maxVal * 100), 4); @endphp
          <div style="flex:1;min-width:36px;display:flex;flex-direction:column;align-items:center;gap:3px;">
            <span style="font-size:9px;color:var(--text3);white-space:nowrap;">{{ $row['jobs'] }}</span>
            <div title="{{ $symbol }} {{ $row['display'] }}"
                 style="width:100%;background:var(--accent);border-radius:4px 4px 0 0;height:{{ $h }}px;opacity:.8;min-height:4px;"></div>
            <span style="font-size:9px;color:var(--text3);white-space:nowrap;">{{ $row['month'] }}</span>
          </div>
          @endforeach
        </div>
      @else
        <p style="color:var(--text3);font-size:13px;text-align:center;padding:20px 0;">No data for this period.</p>
      @endif

    @endif
  </div>
  @endif

@endforeach
@endsection
