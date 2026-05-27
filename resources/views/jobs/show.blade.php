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

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:20px;">
  <div class="stat-card">
    <div class="stat-label">Job Amount</div>
    <div class="stat-value accent" style="font-size:22px;">{{ $bmsCurrency }} {{ number_format($job->amount) }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Amount Paid</div>
    <div class="stat-value" style="font-size:22px;color:var(--green);">{{ $bmsCurrency }} {{ number_format($job->amountPaid()) }}</div>
    <div style="margin-top:8px;">
      @include('partials.job-payment-status', ['job' => $job])
    </div>
    @if($job->balanceDue() > 0)
      <div class="stat-sub" style="margin-top:6px;">Balance: {{ $bmsCurrency }} {{ number_format($job->balanceDue()) }}</div>
    @endif
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
          @include('partials.job-payment-status', ['job' => $job])
          @if($job->paymentStatus() !== 'full')
            <form method="POST" action="{{ route('bms.jobs.update', $job->id) }}" style="display:inline;margin-left:8px;">
              @csrf @method('PUT')
              <input type="hidden" name="amount_paid" value="{{ $job->amount }}">
              <button type="submit" class="btn btn-success btn-sm">Mark Fully Paid</button>
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

@if(!empty($canDeleteJob))
<div style="margin-top:20px;">
  @if(!empty($requiresDeleteOtp))
    <div class="card" style="max-width:520px;border-color:rgba(220,38,38,.35);">
      <div class="card-header"><div class="card-title" style="color:var(--red);">Delete Job</div></div>
      <p style="font-size:13px;color:var(--text2);margin-bottom:14px;">
        Admin approval is required. Request approval, then check your notifications for the delete code.
      </p>
      @if(empty($hasPendingDeleteRequest) && empty($hasPendingDeleteOtp))
        <form method="POST" action="{{ route('bms.jobs.delete-otp.request', $job->id) }}" style="margin-bottom:14px;">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Request delete approval</button>
        </form>
      @elseif(!empty($hasPendingDeleteRequest))
        <div class="alert alert-info" style="margin-bottom:14px;font-size:12px;">
          Waiting for admin approval. You'll receive a notification with your delete code once approved.
          <a href="{{ route('bms.notifications.index') }}" class="text-accent" style="margin-left:6px;">Open notifications</a>
        </div>
      @else
        <div class="alert alert-success" style="margin-bottom:14px;font-size:12px;">
          Your delete code was sent to your notifications.
          <a href="{{ route('bms.notifications.index') }}" class="text-accent" style="margin-left:6px;">View code</a>
        </div>
      @endif
      <form method="POST" action="{{ route('bms.jobs.destroy', $job->id) }}" onsubmit="return confirm('Delete this job permanently?')">
        @csrf @method('DELETE')
        <div class="fld" style="margin-bottom:12px;">
          <label>Delete code</label>
          <input type="text" name="delete_otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" required placeholder="6-digit code from notifications" autocomplete="one-time-code" style="letter-spacing:3px;font-family:var(--mono);max-width:220px;">
        </div>
        <button type="submit" class="btn btn-danger btn-sm">Delete Job</button>
      </form>
    </div>
  @else
    @if(!empty($pendingDeleteRequests))
      <div class="card" style="max-width:520px;margin-bottom:14px;border-color:rgba(234,88,12,.35);">
        <div class="card-header"><div class="card-title" style="color:var(--orange);">Pending delete requests</div></div>
        @foreach($pendingDeleteRequests as $pending)
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
            <div style="font-size:13px;">
              <strong>{{ $pending['requester_name'] }}</strong>
              <span style="color:var(--text3);"> requested to delete this job</span>
            </div>
            <form method="POST" action="{{ route('bms.jobs.delete-otp.approve', $job->id) }}">
              @csrf
              <input type="hidden" name="requester_id" value="{{ $pending['requester_id'] }}">
              <button type="submit" class="btn btn-primary btn-sm">Approve &amp; send code</button>
            </form>
          </div>
        @endforeach
      </div>
    @endif
    <form method="POST" action="{{ route('bms.jobs.destroy', $job->id) }}" onsubmit="return confirm('Delete this job?')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm">Delete Job</button>
    </form>
  @endif
</div>
@endif
@endsection
