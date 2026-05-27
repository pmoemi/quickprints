@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Clients</div>
    <div class="page-subtitle">{{ $clients->count() }} client(s)</div>
  </div>
  <a href="{{ route('bms.clients.create') }}" class="btn btn-primary">+ New Client</a>
</div>

<form method="GET" class="filter-bar">
  <input class="search-input" name="q" value="{{ request('q') }}" placeholder="Search name, phone, email…">
  <button type="submit" class="btn btn-secondary btn-sm">Search</button>
  @if(request('q'))
    <a href="{{ route('bms.clients.index') }}" class="btn btn-secondary btn-sm">Clear</a>
  @endif
</form>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Company</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Branch</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $client)
          <tr>
            <td style="font-weight:600;">{{ $client->name }}</td>
            <td style="color:var(--text2);">{{ $client->company ?? '—' }}</td>
            <td><span class="mono" style="font-size:12px;">{{ $client->phone ?? '—' }}</span></td>
            <td style="font-size:12px;color:var(--text2);">{{ $client->email ?? '—' }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $client->branch ?? '—' }}</span></td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.clients.edit', $client->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.clients.destroy', $client->id) }}" onsubmit="return confirm('Delete client?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6">
              <div class="empty-state"><div class="empty-icon">👥</div><p>No clients found</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
