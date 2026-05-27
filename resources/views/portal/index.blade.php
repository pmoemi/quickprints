@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Client Portal</div>
    <div class="page-subtitle">Manage client portal access links</div>
  </div>
</div>

<div class="card" style="max-width:560px;margin-bottom:20px;">
  <div class="card-header"><div class="card-title">Generate Portal Link</div></div>
  <form method="POST" action="{{ route('bms.portal.store') }}">
    @csrf
    <div class="form-row">
      <div class="fld">
        <label>Client</label>
        <select name="client_id" required>
          <option value="">Select client</option>
          @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
          @endforeach
        </select>
        @error('client_id')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Access Level</label>
        <select name="access_level">
          <option value="basic">Basic (view jobs)</option>
          <option value="full">Full (view + quote)</option>
        </select>
      </div>
      <div class="fld">
        <label>Expires (days)</label>
        <input type="number" name="days" value="{{ old('days', 30) }}" min="1" max="365" placeholder="Blank = no expiry">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Generate Link</button>
  </form>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Client</th><th>Access</th><th>Expires</th><th>Created</th><th>Link</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($tokens as $token)
          @php $expired = $token->expires_at && \Carbon\Carbon::parse($token->expires_at)->isPast(); @endphp
          <tr>
            <td style="font-weight:600;">{{ $token->client?->name ?? '—' }}</td>
            <td><span class="badge badge-gray">{{ $token->access_level }}</span></td>
            <td style="font-size:12px;color:{{ $expired ? 'var(--red)' : 'var(--text3)' }};">
              {{ $token->expires_at ? \Carbon\Carbon::parse($token->expires_at)->format('d M Y') : 'Never' }}
              @if($expired) <span class="badge badge-red" style="margin-left:4px;">Expired</span> @endif
            </td>
            <td style="font-size:12px;color:var(--text3);">{{ \Carbon\Carbon::parse($token->created_at)->format('d M Y') }}</td>
            <td>
              @php $url = route('portal.public', $token->token); @endphp
              <input type="text" value="{{ $url }}" readonly style="height:28px;font-size:11px;width:240px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:4px 8px;color:var(--text2);cursor:pointer;" onclick="this.select();document.execCommand('copy');" title="Click to copy">
            </td>
            <td>
              <form method="POST" action="{{ route('bms.portal.destroy', $token->id) }}" onsubmit="return confirm('Revoke this portal link?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Revoke</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🔗</div><p>No portal links created</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
