@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Assets</div>
    <div class="page-subtitle">{{ $assets->count() }} asset(s) · Value: {{ $bmsCurrency }} {{ number_format($assets->sum('current_value')) }}</div>
  </div>
  <a href="{{ route('bms.assets.create') }}" class="btn btn-primary">+ Add Asset</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Category</th><th>Purchase Cost</th><th>Current Value</th><th>Condition</th><th>Branch</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($assets as $asset)
          <tr>
            <td style="font-weight:600;">{{ $asset->name }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $asset->category }}</span></td>
            <td class="mono">{{ $bmsCurrency }} {{ number_format($asset->purchase_cost ?? 0) }}</td>
            <td class="mono text-green">{{ $bmsCurrency }} {{ number_format($asset->current_value ?? 0) }}</td>
            <td>
              @php $condColors = ['Good'=>'badge-green','Fair'=>'badge-yellow','Poor'=>'badge-red','Under Maintenance'=>'badge-orange']; @endphp
              <span class="badge {{ $condColors[$asset->condition_status ?? 'Good'] ?? 'badge-gray' }}">{{ $asset->condition_status ?? 'Good' }}</span>
            </td>
            <td style="font-size:12px;color:var(--text2);">{{ $asset->branch }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.assets.edit', $asset->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.assets.destroy', $asset->id) }}" onsubmit="return confirm('Delete asset?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏗️</div><p>No assets recorded</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
