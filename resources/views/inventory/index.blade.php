@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Materials / Inventory</div>
    <div class="page-subtitle">{{ $items->count() }} item(s)</div>
  </div>
  <a href="{{ route('bms.inventory.create') }}" class="btn btn-primary">+ Add Item</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Category</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Unit Cost</th>
          <th>Reorder Level</th>
          <th>Branch</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          @php $isLow = $item->qty <= ($item->reorder_level + 1); @endphp
          <tr>
            <td style="font-weight:600;">{{ $item->name }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $item->cat ?? $item->category ?? '—' }}</span></td>
            <td><span class="mono" style="font-size:14px;{{ $isLow ? 'color:var(--red);font-weight:700;' : '' }}">{{ $item->qty }}</span></td>
            <td><span style="font-size:12px;color:var(--text3);">{{ $item->unit }}</span></td>
            <td><span class="mono" style="font-size:12px;">KSh {{ number_format($item->unit_cost ?? 0) }}</span></td>
            <td><span class="mono" style="font-size:12px;">{{ $item->reorder_level }}</span></td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $item->branch ?? 'All' }}</span></td>
            <td>
              @if($isLow)
                <span class="badge badge-red">Low Stock</span>
              @else
                <span class="badge badge-green">OK</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.inventory.edit', $item->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.inventory.destroy', $item->id) }}" onsubmit="return confirm('Delete item?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
              <div class="empty-state"><div class="empty-icon">📦</div><p>No inventory items</p></div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
