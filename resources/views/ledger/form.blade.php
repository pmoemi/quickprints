@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Post Journal Entry</div>
    <div class="page-subtitle">Double-entry bookkeeping</div>
  </div>
  <a href="{{ route('bms.ledger.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:700px;">
  <div class="card-header"><div class="card-title">Journal Entry</div></div>
  <form method="POST" action="{{ route('bms.ledger.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Date</label>
        <input type="date" name="date" value="{{ old('date', $entry->date) }}" required>
        @error('date')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>Reference</label>
        <input type="text" name="ref" value="{{ old('ref') }}" placeholder="INV-001 / MANUAL">
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Description</label>
        <input type="text" name="description" value="{{ old('description') }}" required placeholder="Transaction description">
        @error('description')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
    </div>

    <div class="grid-2" style="margin-bottom:14px;">
      <div style="padding:16px;background:var(--bg3);border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:12px;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Debit</div>
        <div class="form-row">
          <div class="fld">
            <label>Account</label>
            <select name="debit_account" required>
              <option value="">Select account</option>
              @foreach($accounts as $group => $accs)
                <optgroup label="{{ $group }}">
                  @foreach($accs as $acc)
                    <option value="{{ $acc }}" {{ old('debit_account') === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            @error('debit_account')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
          </div>
        </div>
        <div class="form-row">
          <div class="fld">
            <label>Amount</label>
            <input type="number" name="debit_amount" value="{{ old('debit_amount') }}" min="0.01" step="0.01" required>
            @error('debit_amount')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
          </div>
        </div>
      </div>
      <div style="padding:16px;background:var(--bg3);border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:12px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Credit</div>
        <div class="form-row">
          <div class="fld">
            <label>Account</label>
            <select name="credit_account" required>
              <option value="">Select account</option>
              @foreach($accounts as $group => $accs)
                <optgroup label="{{ $group }}">
                  @foreach($accs as $acc)
                    <option value="{{ $acc }}" {{ old('credit_account') === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            @error('credit_account')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
          </div>
        </div>
        <div class="form-row">
          <div class="fld">
            <label>Amount</label>
            <input type="number" name="credit_amount" value="{{ old('credit_amount') }}" min="0.01" step="0.01" required>
            @error('credit_amount')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
          </div>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-primary">Post Entry</button>
      <a href="{{ route('bms.ledger.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
