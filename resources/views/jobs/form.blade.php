@extends('layouts.bms')

@section('content')
@php $editing = $job->exists; @endphp

<div class="page-header">
  <div>
    <div class="page-title">{{ $editing ? 'Edit Job '.$job->id : 'New Job' }}</div>
    <div class="page-subtitle">{{ $editing ? 'Update job details' : 'Create a new print job' }}</div>
  </div>
  <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:900px;">
  <form method="POST" action="{{ $editing ? route('bms.jobs.update', $job->id) : route('bms.jobs.store') }}" id="job-form">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="form-row cols-2">
      <div class="fld">
        <label>Client @if(!$editing)<span style="color:var(--red)">*</span>@endif</label>
        <div style="display:flex;gap:8px;align-items:flex-start;">
          <select name="client_id" id="client-select" @if(!$editing) required @endif style="flex:1;">
            <option value="">— Select client —</option>
            @foreach($clients as $c)
              <option value="{{ $c->id }}" {{ old('client_id', $job->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
          </select>
          @if(!$editing)
            <button type="button" class="btn btn-secondary" id="toggle-new-client" style="white-space:nowrap;">+ New</button>
          @endif
        </div>
        @error('client_id')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>Branch <span style="color:var(--red)">*</span></label>
        @if(count($branches) === 1)
          <input type="hidden" name="branch" id="job-branch" value="{{ $branches[0] }}">
          <input type="text" value="{{ $branches[0] }}" disabled style="opacity:.85;cursor:not-allowed;">
        @else
          <select name="branch" id="job-branch" required>
            @foreach($branches as $br)
              <option value="{{ $br }}" {{ old('branch', $job->branch ?? $branches[0] ?? null) === $br ? 'selected' : '' }}>{{ $br }}</option>
            @endforeach
          </select>
        @endif
      </div>
    </div>

    @if(!$editing)
    <div id="new-client-panel" style="display:none;margin-bottom:16px;padding:16px;background:var(--bg3);border:1px dashed var(--border);border-radius:var(--radius);">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;">
        <div style="font-size:13px;font-weight:600;">Add New Client</div>
        <button type="button" class="btn btn-secondary btn-sm" id="cancel-new-client">Cancel</button>
      </div>
      <div id="new-client-alert" style="display:none;margin-bottom:12px;padding:10px 12px;border-radius:var(--radius);font-size:12px;"></div>
      <div class="form-row cols-2">
        <div class="fld">
          <label>Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" id="new-client-name" placeholder="Client full name">
          <span class="new-client-error" data-field="new_client_name" style="font-size:11px;color:var(--red);"></span>
        </div>
        <div class="fld">
          <label>Company</label>
          <input type="text" id="new-client-company" placeholder="Company name">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="fld">
          <label>Phone</label>
          <input type="tel" id="new-client-phone" placeholder="07xx xxx xxx">
        </div>
        <div class="fld">
          <label>Email</label>
          <input type="email" id="new-client-email" placeholder="client@email.com">
          <span class="new-client-error" data-field="new_client_email" style="font-size:11px;color:var(--red);"></span>
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:4px;">
        <button type="button" class="btn btn-primary btn-sm" id="save-new-client">Save Client</button>
      </div>
    </div>
    @endif

    <div class="form-row">
      <div class="fld">
        <label>Job Title / Description <span style="color:var(--red)">*</span></label>
        <input type="text" name="title" value="{{ old('title', $job->title) }}" placeholder="e.g. Vinyl Banner 3x1m" required>
      </div>
    </div>

    <div class="form-row cols-3">
      <div class="fld">
        <label>Category</label>
        <select name="category">
          <option value="">— Select —</option>
          @foreach($bmsJobCategories as $cat)
            <option value="{{ $cat }}" {{ old('category', $job->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="fld">
        <label>Amount ({{ $bmsCurrency }})</label>
        <input type="number" name="amount" value="{{ old('amount', $job->amount) }}" placeholder="0" min="0" step="0.01">
      </div>
      <div class="fld">
        <label>Priority</label>
        <select name="priority">
          @foreach(['low','medium','high'] as $p)
            <option value="{{ $p }}" {{ old('priority', $job->priority ?? 'medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-row cols-3">
      @if($editing)
      <div class="fld">
        <label>Stage</label>
        <select name="stage">
          @foreach(['waiting','designing','approval','printing','fabrication','ready','installed','paid'] as $s)
            <option value="{{ $s }}" {{ old('stage', $job->stage ?? 'designing') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      @else
      <input type="hidden" name="stage" value="designing">
      <div class="fld">
        <label>Stage</label>
        <input type="text" value="Designing" disabled style="opacity:.85;cursor:not-allowed;">
        <div style="font-size:11px;color:var(--text3);margin-top:4px;">New jobs go straight to the designer board.</div>
      </div>
      @endif
      <div class="fld">
        <label>Assigned Designer <span style="color:var(--red)">*</span></label>
        <select name="designer_id" required>
          <option value="">— Select designer —</option>
          @foreach($designers as $d)
            <option value="{{ $d->id }}" {{ old('designer_id', $job->designer_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
          @endforeach
        </select>
        @error('designer_id')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
      </div>
      <div class="fld">
        <label>Deadline</label>
        <input type="date" name="deadline" value="{{ old('deadline', $job->deadline?->format('Y-m-d')) }}">
      </div>
    </div>

    @if($editing)
    <div class="form-row cols-2">
      <div class="fld">
        <label>Total Amount Paid ({{ $bmsCurrency }})</label>
        <input type="number" name="amount_paid" value="{{ old('amount_paid', $job->amountPaid()) }}" min="0" step="0.01" placeholder="0">
        @error('amount_paid')<span style="font-size:11px;color:var(--red);">{{ $message }}</span>@enderror
        <div style="margin-top:8px;">
          @include('partials.job-payment-status', ['job' => $job])
        </div>
      </div>
      <div class="fld">
        <label>Balance Due</label>
        <div style="padding:10px 12px;background:var(--bg3);border-radius:var(--radius);font-size:15px;font-weight:600;margin-top:2px;">
          {{ $bmsCurrency }} {{ number_format($job->balanceDue()) }}
        </div>
      </div>
    </div>
    @endif

    <div class="form-row">
      <div class="fld">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Additional notes…">{{ old('notes', $job->notes) }}</textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
      <a href="{{ route('bms.jobs.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Job' : 'Create Job' }}</button>
    </div>
  </form>
</div>

@if(!$editing)
<script>
(function () {
  var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
  var quickUrl = @json(route('bms.jobs.clients.quick'));
  var select = document.getElementById('client-select');
  var branchInput = document.getElementById('job-branch');
  var panel = document.getElementById('new-client-panel');
  var toggleBtn = document.getElementById('toggle-new-client');
  var cancelBtn = document.getElementById('cancel-new-client');
  var saveBtn = document.getElementById('save-new-client');
  var alertBox = document.getElementById('new-client-alert');
  var nameInput = document.getElementById('new-client-name');
  var companyInput = document.getElementById('new-client-company');
  var phoneInput = document.getElementById('new-client-phone');
  var emailInput = document.getElementById('new-client-email');

  if (!select || !panel || !saveBtn) return;

  function clearNewClientErrors() {
    document.querySelectorAll('.new-client-error').forEach(function (el) { el.textContent = ''; });
    if (alertBox) {
      alertBox.style.display = 'none';
      alertBox.textContent = '';
      alertBox.style.background = '';
      alertBox.style.color = '';
    }
  }

  function showAlert(message, type) {
    if (!alertBox) return;
    alertBox.textContent = message;
    alertBox.style.display = 'block';
    alertBox.style.background = type === 'success' ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)';
    alertBox.style.color = type === 'success' ? '#166534' : '#b91c1c';
  }

  function showValidationErrors(errors) {
    clearNewClientErrors();
    Object.keys(errors || {}).forEach(function (field) {
      var el = document.querySelector('.new-client-error[data-field="' + field + '"]');
      if (el && errors[field] && errors[field][0]) {
        el.textContent = errors[field][0];
      }
    });
    showAlert('Please fix the highlighted fields.', 'error');
  }

  function clearNewClientFields() {
    if (nameInput) nameInput.value = '';
    if (companyInput) companyInput.value = '';
    if (phoneInput) phoneInput.value = '';
    if (emailInput) emailInput.value = '';
  }

  function openPanel() {
    clearNewClientErrors();
    panel.style.display = 'block';
    if (nameInput) nameInput.focus();
  }

  function closePanel() {
    panel.style.display = 'none';
    clearNewClientErrors();
  }

  function addClientOption(client) {
    var existing = select.querySelector('option[value="' + client.id + '"]');
    if (existing) {
      select.value = String(client.id);
      return;
    }

    var option = document.createElement('option');
    option.value = String(client.id);
    option.textContent = client.name;
    option.selected = true;

    var options = Array.from(select.options).slice(1);
    options.push(option);
    options.sort(function (a, b) {
      return a.textContent.localeCompare(b.textContent, undefined, { sensitivity: 'base' });
    });

    select.innerHTML = '<option value="">— Select client —</option>';
    options.forEach(function (opt) { select.appendChild(opt); });
    select.value = String(client.id);
  }

  toggleBtn?.addEventListener('click', function () {
    if (panel.style.display === 'none') {
      openPanel();
    } else {
      closePanel();
    }
  });

  cancelBtn?.addEventListener('click', closePanel);

  saveBtn.addEventListener('click', function () {
    clearNewClientErrors();

    var branch = branchInput ? branchInput.value : '';
    if (!branch) {
      showAlert('Select a branch before adding a client.', 'error');
      return;
    }

    if (!nameInput?.value.trim()) {
      showValidationErrors({ new_client_name: ['Client name is required.'] });
      nameInput?.focus();
      return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';

    fetch(quickUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        branch: branch,
        new_client_name: nameInput.value.trim(),
        new_client_company: companyInput?.value.trim() || null,
        new_client_phone: phoneInput?.value.trim() || null,
        new_client_email: emailInput?.value.trim() || null,
      }),
    })
      .then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          if (result.data?.errors) {
            showValidationErrors(result.data.errors);
          } else {
            showAlert(result.data?.message || 'Could not save client.', 'error');
          }
          return;
        }

        addClientOption(result.data.client);
        clearNewClientFields();
        showAlert((result.data.client.name || 'Client') + ' added and selected.', 'success');
        setTimeout(closePanel, 900);
      })
      .catch(function () {
        showAlert('Network error. Please try again.', 'error');
      })
      .finally(function () {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Client';
      });
  });
})();
</script>
@endif
@endsection
