@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Suppliers</div>
    <div class="page-subtitle">{{ $items->count() }} supplier(s)</div>
  </div>
  <a href="{{ route('bms.suppliers.create') }}" class="btn btn-primary">+ Add Supplier</a>
</div>

<div class="card">
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Contact</th><th>Phone</th><th>Category</th><th>Payment Terms</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($items as $supplier)
          <tr>
            <td style="font-weight:600;">{{ $supplier->name }}</td>
            <td style="font-size:12px;color:var(--text2);">{{ $supplier->contact ?? '—' }}</td>
            <td class="mono" style="font-size:12px;">{{ $supplier->phone ?? '—' }}</td>
            <td><span style="font-size:12px;color:var(--text2);">{{ $supplier->category ?? '—' }}</span></td>
            <td><span class="badge badge-gray">{{ $supplier->payment_terms ?? '—' }}</span></td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('bms.suppliers.edit', $supplier->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                <form method="POST" action="{{ route('bms.suppliers.destroy', $supplier->id) }}" onsubmit="return confirm('Delete supplier?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏭</div><p>No suppliers added</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
