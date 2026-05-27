@extends('layouts.bms')

@section('content')
@php $client = $clients[$job->client_id] ?? null; @endphp

<div class="page-header">
  <div>
    <div class="page-title" style="font-family:var(--mono);">{{ $job->id }}</div>
    <div class="page-subtitle">{{ $job->title }}</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="{{ route('bms.jobs.invoice', $job->id) }}" class="btn btn-secondary btn-sm" target="_blank">🖨 Invoice</a>
    <a href="{{ route('bms.jobs.edit', $job->id) }}" class="btn btn-secondary">Edit</a>
    <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary">← Back</a>
  </div>
</div>

<div class="grid-4" style="margin-bottom:20px;">
  <div class="stat-card">
    <div class="stat-label">Amount</div>
    <div class="stat-value accent" style="font-size:22px;">KSh {{ number_format($job->amount) }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Branch</div>
    <div style="font-size:16px;font-weight:600;margin-top:6px;">{{ $job->branch }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Priority</div>
    <div style="margin-top:8px;">
      @if($job->priority === 'high') <span class="p-high" style="font-size:16px;">HIGH</span>
      @elseif($job->priority === 'medium') <span class="p-medium" style="font-size:16px;">MED</span>
      @else <span class="p-low" style="font-size:16px;">LOW</span>
      @endif
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Deadline</div>
    <div style="font-size:14px;color:var(--yellow);font-weight:600;margin-top:8px;">
      {{ $job->deadline ? $job->deadline->format('d M Y') : '—' }}
    </div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:20px;">
  <div class="card">
    <div class="card-header"><div class="card-title">Job Details</div></div>
    <table style="width:100%;border:none;">
      <tr>
        <td style="width:140px;font-size:12px;color:var(--text3);padding:8px 0;border:none;">Client</td>
        <td style="font-size:13px;border:none;">{{ $client?->name ?? '—' }}</td>
      </tr>
      <tr>
        <td style="font-size:12px;color:var(--text3);padding:8px 0;border:none;">Category</td>
        <td style="font-size:13px;border:none;">{{ $job->category ?? '—' }}</td>
      </tr>
      <tr>
        <td style="font-size:12px;color:var(--text3);padding:8px 0;border:none;">Stage</td>
        <td style="border:none;"><span class="badge stage-{{ $job->stage }}">{{ $job->stage }}</span></td>
      </tr>
      <tr>
        <td style="font-size:12px;color:var(--text3);padding:8px 0;border:none;">Payment</td>
        <td style="border:none;">
          @if($job->paid)
            <span class="badge badge-green">✓ Paid</span>
          @else
            <span class="badge badge-red">Unpaid</span>
            <form method="POST" action="{{ route('bms.jobs.update', $job->id) }}" style="display:inline;margin-left:8px;">
              @csrf @method('PUT')
              <input type="hidden" name="paid" value="1">
              <button type="submit" class="btn btn-success btn-sm">Mark as Paid</button>
            </form>
          @endif
        </td>
      </tr>
      @if($job->notes)
      <tr>
        <td style="font-size:12px;color:var(--text3);padding:8px 0;border:none;">Notes</td>
        <td style="font-size:13px;border:none;color:var(--text2);">{{ $job->notes }}</td>
      </tr>
      @endif
    </table>

    <div style="margin-top:16px;display:flex;gap:8px;">
      <form method="POST" action="{{ route('bms.jobs.update', $job->id) }}" style="display:flex;gap:8px;align-items:center;">
        @csrf @method('PUT')
        <select name="stage" class="filter-select" style="height:32px;">
          @foreach(['waiting','designing','approval','printing','fabrication','ready','installed','paid'] as $s)
            <option value="{{ $s }}" {{ $job->stage === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Update Stage</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">Job History</div>
    </div>
    <div class="timeline">
      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-content">
          <div class="tl-action">Job created</div>
          <div class="tl-meta">Stage: waiting</div>
        </div>
      </div>
      @foreach($job->history ?? [] as $h)
        <div class="tl-item">
          <div class="tl-dot"></div>
          <div class="tl-content">
            <div class="tl-action">{{ $h['action'] ?? '' }}</div>
            <div class="tl-meta">{{ $h['by'] ?? '' }} · {{ isset($h['at']) ? \Carbon\Carbon::parse($h['at'])->format('d M Y H:i') : '' }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div style="display:flex;gap:10px;">
  <form method="POST" action="{{ route('bms.jobs.destroy', $job->id) }}" onsubmit="return confirm('Delete this job?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Delete Job</button>
  </form>
</div>
@endsection
