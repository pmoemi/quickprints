@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Designer Board</div>
    <div class="page-subtitle">{{ $jobs->count() }} job(s) awaiting design</div>
  </div>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Job ID</th><th>Title</th><th>Client</th><th>Branch</th>@if($showDesignerColumn ?? false)<th>Designer</th>@endif<th>Stage</th><th>Priority</th><th>Deadline</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($jobs as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $job->id }}</td>
            <td style="font-weight:600;">{{ $job->title }}</td>
            <td>{{ $client?->name ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $job->branch }}</td>
            @if($showDesignerColumn ?? false)
            <td style="font-size:12px;">{{ $designers[$job->designer_id]->name ?? '—' }}</td>
            @endif
            <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
            <td>
              @if($job->priority === 'high') <span class="p-high">HIGH</span>
              @elseif($job->priority === 'medium') <span class="p-medium">MED</span>
              @else <span class="p-low">LOW</span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--yellow);">{{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.jobs.show', $job->id) }}" class="btn btn-secondary btn-sm">View</a>
                <form method="POST" action="{{ route('bms.jobs.stage', $job->id) }}">
                  @csrf @method('PATCH')
                  <select name="stage" onchange="this.form.submit()" class="filter-select" style="height:30px;font-size:12px;">
                    @foreach(['waiting','designing','approval','printing'] as $s)
                      <option value="{{ $s }}" {{ $job->stage === $s ? 'selected' : '' }}>→ {{ ucfirst($s) }}</option>
                    @endforeach
                  </select>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ ($showDesignerColumn ?? false) ? 9 : 8 }}"><div class="empty-state"><div class="empty-icon">🎨</div><p>No jobs in design queue</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
