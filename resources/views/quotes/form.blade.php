@extends('layouts.bms')

@section('content')
@php $editing = $quote->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Quote' : 'New Quote' }}</div>
  </div>
  <a href="{{ route('bms.quotes.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:860px;">
  <form method="POST" action="{{ $editing ? route('bms.quotes.update', $quote->id) : route('bms.quotes.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-3">
      <div class="fld">
        <label>Client Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="client_name" value="{{ old('client_name', $quote->client_name) }}" required placeholder="Client name">
      </div>
      <div class="fld">
        <label>Client Phone</label>
        <input type="tel" name="client_phone" value="{{ old('client_phone', $quote->client_phone) }}" placeholder="07xx xxx xxx">
      </div>
      <div class="fld">
        <label>Date <span style="color:var(--red)">*</span></label>
        <input type="date" name="date" value="{{ old('date', $quote->date ?? now()->toDateString()) }}" required>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Branch <span style="color:var(--red)">*</span></label>
        <select name="branch" required>
          @foreach($branches as $br)
            <option value="{{ $br }}" {{ old('branch', $quote->branch) === $br ? 'selected' : '' }}>{{ $br }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          @foreach(['draft','sent','approved','declined'] as $s)
            <option value="{{ $s }}" {{ old('status', $quote->status ?? 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>VAT Rate (%)</label>
        <input type="number" name="vat_rate" value="{{ old('vat_rate', $quote->vat_rate ?? 16) }}" min="0" max="100">
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <div style="font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Quote Items</div>
      <div id="quote-items">
        @php $items = old('items', $quote->items ?? [['desc'=>'','qty'=>1,'unit_price'=>0]]); @endphp
        @foreach($items as $idx => $item)
          <div class="form-row" style="grid-template-columns:2fr 1fr 1fr 30px;gap:8px;margin-bottom:8px;" id="item-{{ $idx }}">
            <div class="fld" style="margin-bottom:0;">
              <input type="text" name="items[{{ $idx }}][desc]" value="{{ $item['desc'] ?? '' }}" placeholder="Item description">
            </div>
            <div class="fld" style="margin-bottom:0;">
              <input type="number" name="items[{{ $idx }}][qty]" value="{{ $item['qty'] ?? 1 }}" min="1" placeholder="Qty">
            </div>
            <div class="fld" style="margin-bottom:0;">
              <input type="number" name="items[{{ $idx }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" placeholder="Unit Price">
            </div>
            <button type="button" onclick="this.closest('[id^=item]').remove()" style="background:var(--red-dim);color:var(--red);border:none;border-radius:4px;cursor:pointer;font-size:16px;width:30px;height:38px;margin-top:0;">×</button>
          </div>
        @endforeach
      </div>
      <button type="button" onclick="addItem()" class="btn btn-secondary btn-sm" style="margin-top:4px;">+ Add Item</button>
    </div>

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="2" placeholder="Optional notes…">{{ old('notes', $quote->notes) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.quotes.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Quote' : 'Create Quote' }}</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
let itemCount = {{ count($items ?? []) }};
function addItem() {
  const container = document.getElementById('quote-items');
  const div = document.createElement('div');
  div.className = 'form-row';
  div.style.cssText = 'grid-template-columns:2fr 1fr 1fr 30px;gap:8px;margin-bottom:8px;';
  div.id = 'item-' + itemCount;
  div.innerHTML = `
    <div class="fld" style="margin-bottom:0;"><input type="text" name="items[${itemCount}][desc]" placeholder="Item description"></div>
    <div class="fld" style="margin-bottom:0;"><input type="number" name="items[${itemCount}][qty]" value="1" min="1" placeholder="Qty"></div>
    <div class="fld" style="margin-bottom:0;"><input type="number" name="items[${itemCount}][unit_price]" value="0" min="0" step="0.01" placeholder="Unit Price"></div>
    <button type="button" onclick="this.closest('[id^=item]').remove()" style="background:var(--red-dim);color:var(--red);border:none;border-radius:4px;cursor:pointer;font-size:16px;width:30px;height:38px;">×</button>
  `;
  container.appendChild(div);
  itemCount++;
}
</script>
@endpush
@endsection
