@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Leads</div>
    <div class="page-subtitle">{{ $leads->count() }} lead(s)</div>
  </div>
  <a href="{{ route('bms.leads.create') }}" class="btn btn-primary">+ New Lead</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Phone</th>
          <th>Service</th>
          <th>Value</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Branch</th>
          <th>Follow Up</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads as $lead)
          @php
            $statusColors = ['new'=>'badge-blue','contacted'=>'badge-purple','qualified'=>'badge-yellow','proposal'=>'badge-orange','won'=>'badge-green','lost'=>'badge-red'];
          @endphp
          <tr>
            <td style="font-weight:600;">{{ $lead->client_name }}</td>
            <td class="mono" style="font-size:12px;">{{ $lead->phone ?? '—' }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $lead->service ?? '—' }}</span></td>
            <td><span class="mono">KSh {{ number_format($lead->value ?? 0) }}</span></td>
            <td>
              <span class="badge {{ $statusColors[$lead->status ?? 'new'] ?? 'badge-gray' }}">{{ ucfirst($lead->status ?? 'new') }}</span>
            </td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $lead->assigned_to ?? '—' }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $lead->branch ?? '—' }}</span></td>
            <td style="font-size:12px;color:var(--yellow);">{{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') : '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.leads.edit', $lead->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.leads.destroy', $lead->id) }}" onsubmit="return confirm('Delete lead?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
              <div class="empty-state"><div class="empty-icon">🎯</div><p>No leads yet</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
