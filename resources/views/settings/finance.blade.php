@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

<div class="grid-2">
  <div class="card">
    <div class="card-header"><div class="card-title">VAT Settings</div></div>
    <form method="POST" action="{{ route('bms.settings.finance.update') }}">
      @csrf @method('PUT')
      <div class="form-row">
        <div class="fld">
          <label>VAT Rate (%)</label>
          <input type="number" name="vat_rate" value="{{ old('vat_rate', $settings['vat_rate'] ?? 16) }}" min="0" max="100" step="0.1" required>
          @error('vat_rate')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        </div>
      </div>
      <div class="form-row">
        <div class="fld">
          <label>Currency</label>
          <select name="currency">
            @foreach(['KES' => 'KES - Kenya Shilling', 'USD' => 'USD - US Dollar', 'GBP' => 'GBP - British Pound', 'EUR' => 'EUR - Euro', 'UGX' => 'UGX - Uganda Shilling', 'TZS' => 'TZS - Tanzania Shilling'] as $code => $label)
              <option value="{{ $code }}" {{ old('currency', $settings['currency'] ?? 'KES') === $code ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="fld">
          <label>Currency Symbol</label>
          <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'KES') }}" maxlength="10" required placeholder="KES">
        </div>
      </div>
      <div class="form-row">
        <div class="fld">
          <label>Payment Methods</label>
          <input type="text" name="payment_methods_raw"
                 value="{{ old('payment_methods_raw', implode(', ', $settings['payment_methods'] ?? ['Mpesa','Cash','Bank Transfer','Cheque','Credit'])) }}"
                 placeholder="Mpesa, Cash, Bank Transfer, Cheque, Credit">
          <div style="font-size:11px;color:var(--text3);margin-top:4px;">Comma-separated. These appear in dropdowns across the BMS.</div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Current Values</div></div>
    <div class="activity-item">
      <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;width:120px;flex-shrink:0;">VAT Rate</div>
      <div style="font-size:13px;color:var(--text);">{{ $settings['vat_rate'] ?? 16 }}%</div>
    </div>
    <div class="activity-item">
      <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;width:120px;flex-shrink:0;">Currency</div>
      <div style="font-size:13px;color:var(--text);">{{ $settings['currency'] ?? 'KES' }}</div>
    </div>
    <div class="activity-item">
      <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;width:120px;flex-shrink:0;">Symbol</div>
      <div style="font-size:13px;color:var(--text);">{{ $settings['currency_symbol'] ?? 'KES' }}</div>
    </div>
    <div class="activity-item">
      <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;width:120px;flex-shrink:0;">Pay Methods</div>
      <div style="font-size:13px;color:var(--text);">{{ implode(', ', $settings['payment_methods'] ?? []) ?: '—' }}</div>
    </div>
  </div>
</div>
@endsection
