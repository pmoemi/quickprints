@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Services Catalogue</div>
    <div class="page-subtitle">{{ $items->count() }} service(s) across {{ $catalog->count() }} categories</div>
  </div>
  <a href="{{ route('bms.services.create') }}" class="btn btn-primary">+ Add Service</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Category</th>
          <th>Service Name</th>
          <th style="width:80px;text-align:center;">Order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($catalog as $category => $categoryItems)
          @foreach($categoryItems as $i => $item)
            <tr>
              @if($i === 0)
                <td rowspan="{{ $categoryItems->count() }}" style="font-weight:700;color:var(--accent);vertical-align:top;padding-top:14px;border-right:1px solid var(--border);">
                  {{ $category }}
                  <div style="font-size:11px;color:var(--text3);font-weight:400;">{{ $categoryItems->count() }} item(s)</div>
                </td>
              @endif
              <td>{{ $item->name }}</td>
              <td style="text-align:center;"><span class="mono" style="font-size:12px;color:var(--text3);">{{ $item->sort_order }}</span></td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="{{ route('bms.services.edit', $item->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                  <form method="POST" action="{{ route('bms.services.destroy', $item->id) }}" onsubmit="return confirm('Delete this service?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        @empty
          <tr>
            <td colspan="4">
              <div class="empty-state"><div class="empty-icon">📋</div><p>No services in catalogue</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
