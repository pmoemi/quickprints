@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Operator Queue</div>
    <div class="page-subtitle">{{ $jobs->count() }} job(s) in production</div>
  </div>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Job ID</th><th>Title</th><th>Client</th><th>Branch</th><th>Stage</th><th>Category</th><th>Priority</th><th>Deadline</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($jobs as $job)
          @php $client = $clients[$job->client_id] ?? null; @endphp
          <tr>
            <td class="mono text-accent" style="font-size:12px;">{{ $job->id }}</td>
            <td style="font-weight:600;">{{ $job->title }}</td>
            <td>{{ $client?->name ?? '—' }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $job->branch }}</td>
            <td><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
            <td style="font-size:12px;color:var(--text2);">{{ $job->category ?? '—' }}</td>
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
                    @foreach(['printing','fabrication','ready','installed'] as $s)
                      <option value="{{ $s }}" {{ $job->stage === $s ? 'selected' : '' }}>→ {{ ucfirst($s) }}</option>
                    @endforeach
                  </select>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">⚙️</div><p>No jobs in operator queue</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
