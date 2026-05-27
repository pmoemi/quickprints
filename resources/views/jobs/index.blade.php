@extends('layouts.bms')

@section('content')
@php
  $stages = ['waiting','designing','approval','printing','fabrication','ready','installed','paid'];
  $currentStage = request('stage', 'all');
  $q = request('q', '');
@endphp

<div class="page-header">
  <div>
    <div class="page-title">Job Tracker</div>
    <div class="page-subtitle">{{ $jobs->count() }} job(s)</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="{{ route('bms.jobs.index') }}?export=csv" class="btn btn-secondary btn-sm">⬇ CSV</a>
    <a href="{{ route('bms.jobs.create') }}" class="btn btn-primary">+ New Job</a>
  </div>
</div>

<form method="GET" action="{{ route('bms.jobs.index') }}" class="filter-bar">
  <input class="search-input" name="q" value="{{ $q }}" placeholder="Search job ID, title, client…">
  <select class="filter-select" name="stage" onchange="this.form.submit()">
    <option value="all" {{ $currentStage === 'all' ? 'selected' : '' }}>All Stages</option>
    @foreach($stages as $s)
      <option value="{{ $s }}" {{ $currentStage === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-secondary btn-sm">Search</button>
  @if($q || $currentStage !== 'all')
    <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary btn-sm">Clear</a>
  @endif
</form>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Job ID</th>
          <th>Title</th>
          <th>Client</th>
          <th>Branch</th>
          <th>Stage</th>
          <th>Amount</th>
          <th>Payment</th>
          <th>Priority</th>
          <th>Deadline</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($jobs as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          @php
            // Split ID into parts: e.g. QP-WES-00001 → prefix, branch, number
            $idParts = explode('-', $job->id);
            $idNum   = end($idParts);
            $idHead  = implode('-', array_slice($idParts, 0, count($idParts) - 1));
          @endphp
          <tr>
            <td>
              <a href="{{ route('bms.jobs.show', $job->id) }}" style="text-decoration:none;">
                <span class="mono" style="font-size:11px;color:var(--text3);">{{ $idHead }}-</span><span class="mono" style="font-size:13px;font-weight:700;color:var(--accent);">{{ $idNum }}</span>
              </a>
            </td>
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $job->title }}</td>
            <td>{{ $client?->name ?? '—' }}</td>
            <td><span style="font-size:11px;color:var(--text3);font-family:var(--mono);">{{ $job->branch }}</span></td>
            <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
            <td><span class="mono">{{ $bmsCurrency }} {{ number_format($job->amount) }}</span></td>
            <td>
              @include('partials.job-payment-status', ['job' => $job])
            </td>
            <td>
              @if($job->priority === 'high') <span class="p-high">HIGH</span>
              @elseif($job->priority === 'medium') <span class="p-medium">MED</span>
              @else <span class="p-low">LOW</span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--text2);">
              {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('d M Y') : '—' }}
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.jobs.show', $job->id) }}" class="btn btn-secondary btn-sm">View</a>
                <a href="{{ route('bms.jobs.invoice', $job->id) }}" class="btn btn-secondary btn-sm" title="Invoice">Invoice</a>
                <a href="{{ route('bms.jobs.edit', $job->id) }}" class="btn btn-secondary btn-sm">Edit</a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10">
              <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p>No jobs found</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
