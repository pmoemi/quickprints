@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

<div class="grid-2">
  <div class="card">
    <div class="card-header"><div class="card-title">Company Info</div></div>
    <form method="POST" action="{{ route('bms.settings.company.update') }}">
      @csrf @method('PUT')
      <div class="form-row">
        <div class="fld">
          <label>Company Name</label>
          <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
          @error('company_name')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        </div>
      </div>
      <div class="form-row">
        <div class="fld">
          <label>Tagline</label>
          <input type="text" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}" placeholder="e.g. Premium Print Solutions">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="fld">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}" placeholder="info@company.com">
        </div>
        <div class="fld">
          <label>Phone</label>
          <input type="text" name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}" placeholder="+254 700 000 000">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="fld">
          <label>Website</label>
          <input type="text" name="website" value="{{ old('website', $settings['website'] ?? '') }}" placeholder="www.company.com">
        </div>
        <div class="fld">
          <label>Address</label>
          <input type="text" name="address" value="{{ old('address', $settings['address'] ?? '') }}" placeholder="Nairobi, Kenya">
        </div>
      </div>
      <div class="form-row">
        <div class="fld">
          <label>System Default Theme</label>
          <select name="theme">
            <option value="dark" {{ ($settings['theme'] ?? 'dark') === 'dark' ? 'selected' : '' }}>Dark</option>
            <option value="light" {{ ($settings['theme'] ?? 'dark') === 'light' ? 'selected' : '' }}>Light</option>
          </select>
          <small style="color:var(--text3);">Default for new users. Each user can override with the toolbar toggle.</small>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Current Values</div></div>
    @php $fields = ['Company' => $settings['company_name'] ?? '—', 'Tagline' => $settings['company_tagline'] ?? '—', 'Email' => $settings['email'] ?? '—', 'Phone' => $settings['phone'] ?? '—', 'Website' => $settings['website'] ?? '—', 'Address' => $settings['address'] ?? '—']; @endphp
    @foreach($fields as $label => $val)
      <div class="activity-item">
        <div class="activity-text" style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;width:80px;flex-shrink:0;">{{ $label }}</div>
        <div style="font-size:13px;color:var(--text);">{{ $val }}</div>
      </div>
    @endforeach
  </div>
</div>
@endsection
