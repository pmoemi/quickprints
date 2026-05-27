@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Settings</div>
</div>

@include('settings.tabs')

@php $branches = $settings['branches'] ?? []; @endphp

<div class="grid-2">
  <div class="card">
    <div class="card-header"><div class="card-title">Branches</div></div>
    @forelse($branches as $branch)
      <div class="activity-item">
        <div class="activity-text" style="font-weight:600;">{{ $branch }}</div>
        <div style="display:flex;gap:6px;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="this.closest('.activity-item').nextElementSibling.style.display='block';this.closest('.activity-item').style.display='none'">Rename</button>
          <form method="POST" action="{{ route('bms.settings.branches.destroy', $branch) }}" onsubmit="return confirm('Remove this branch?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Del</button>
          </form>
        </div>
      </div>
      <div style="display:none;padding:8px 0;">
        <form method="POST" action="{{ route('bms.settings.branches.rename') }}" style="display:flex;gap:6px;align-items:center;">
          @csrf @method('PUT')
          <input type="hidden" name="from" value="{{ $branch }}">
          <div class="fld" style="flex:1;margin-bottom:0;">
            <input type="text" name="to" value="{{ $branch }}" required>
          </div>
          <button type="submit" class="btn btn-success btn-sm">OK</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="this.closest('div').style.display='none';this.closest('div').previousElementSibling.style.display='flex'">✕</button>
        </form>
      </div>
    @empty
      <p style="font-size:13px;color:var(--text3);">No branches defined.</p>
    @endforelse
    @error('branch')<div class="alert alert-danger" style="margin-top:12px;">{{ $message }}</div>@enderror
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">Add Branch</div></div>
    <form method="POST" action="{{ route('bms.settings.branches.store') }}">
      @csrf
      <div class="form-row">
        <div class="fld">
          <label>Branch Name</label>
          <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Westlands" required>
          @error('name')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Add Branch</button>
    </form>
  </div>
</div>
@endsection
