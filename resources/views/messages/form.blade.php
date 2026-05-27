@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">Compose Message</div></div>
  <a href="{{ route('bms.messages.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:680px;">
  <form method="POST" action="{{ route('bms.messages.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>To (User)</label>
        <select name="to_user_id">
          <option value="">— Select user —</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" {{ old('to_user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Or To (Role)</label>
        <select name="to_role">
          <option value="">— Select role —</option>
          @foreach($roles as $r)
            <option value="{{ $r }}" {{ old('to_role') === $r ? 'selected' : '' }}>{{ $r }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Subject <span style="color:var(--red)">*</span></label>
        <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Message subject">
      </div>
    </div>
    <div class="form-row">
      <div class="fld">
        <label>Message <span style="color:var(--red)">*</span></label>
        <textarea name="body" rows="6" required placeholder="Type your message…">{{ old('body') }}</textarea>
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.messages.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Send Message</button>
    </div>
  </form>
</div>
@endsection
