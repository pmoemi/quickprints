@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Kanban Board</div>
    <div class="page-subtitle">{{ $jobs->flatten()->count() }} total jobs</div>
  </div>
  <a href="{{ route('bms.jobs.create') }}" class="btn btn-primary">+ New Job</a>
</div>

<div class="kanban-wrap">
  @foreach($stages as $stage)
    @php $stageJobs = $jobs->get($stage, collect()); @endphp
    <div class="kanban-col">
      <div class="kanban-col-header">
        <span class="kanban-col-title badge stage-{{ $stage }}" style="border-radius:4px;padding:3px 8px;">{{ ucfirst($stage) }}</span>
        <span class="kanban-col-count">{{ $stageJobs->count() }}</span>
      </div>
      <div class="kanban-cards">
        @forelse($stageJobs as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          <a href="{{ route('bms.jobs.show', $job->id) }}" class="k-card" style="text-decoration:none;">
            <div class="k-card-id">{{ $job->id }}</div>
            <div class="k-card-title">{{ $job->title }}</div>
            <div class="k-card-client">{{ $client?->name ?? '—' }}</div>
            <div class="k-card-footer">
              <span class="k-card-amount">{{ $bmsCurrency }} {{ number_format($job->amount) }}</span>
              <span class="priority-dot priority-{{ $job->priority ?? 'low' }}" title="{{ $job->priority }}"></span>
            </div>
          </a>
        @empty
          <div class="empty-state" style="padding:20px 10px;">
            <p style="font-size:12px;">No jobs</p>
          </div>
        @endforelse
      </div>
    </div>
  @endforeach
</div>
@endsection
