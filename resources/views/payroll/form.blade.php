@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div><div class="page-title">Add Payroll Entry</div></div>
  <a href="{{ route('bms.payroll.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:700px;">
  <form method="POST" action="{{ route('bms.payroll.store') }}">
    @csrf
    <div class="form-row cols-2">
      <div class="fld">
        <label>Month <span style="color:var(--red)">*</span></label>
        <input type="month" name="month" value="{{ old('month', $entry->month ?? now()->format('Y-m')) }}" required>
      </div>
      <div class="fld">
        <label>Staff Member <span style="color:var(--red)">*</span></label>
        <select name="staff_id" required onchange="fillStaffName(this)">
          <option value="">— Select —</option>
          @foreach($staff as $s)
            <option value="{{ $s->id }}" data-name="{{ $s->name }}" data-salary="{{ $s->salary ?? 0 }}"
              {{ old('staff_id', $entry->staff_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>
        <input type="hidden" name="staff_name" id="staff_name_hidden" value="{{ old('staff_name', $entry->staff_name) }}">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Gross Salary (KSh)</label>
        <input type="number" name="gross_salary" id="gross_salary" value="{{ old('gross_salary', $entry->gross_salary) }}" min="0" step="0.01" placeholder="0" oninput="calcNet()">
      </div>
      <div class="fld">
        <label>Status</label>
        <select name="status">
          <option value="pending" {{ old('status', $entry->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="paid" {{ old('status', $entry->status) === 'paid' ? 'selected' : '' }}>Paid</option>
        </select>
      </div>
    </div>
    <div class="form-row cols-3">
      <div class="fld">
        <label>NHIF</label>
        <input type="number" name="nhif" id="nhif" value="{{ old('nhif', $entry->nhif ?? 1700) }}" min="0" step="0.01" oninput="calcNet()">
      </div>
      <div class="fld">
        <label>NSSF</label>
        <input type="number" name="nssf" id="nssf" value="{{ old('nssf', $entry->nssf ?? 600) }}" min="0" step="0.01" oninput="calcNet()">
      </div>
      <div class="fld">
        <label>PAYE</label>
        <input type="number" name="paye" id="paye" value="{{ old('paye', $entry->paye ?? 0) }}" min="0" step="0.01" oninput="calcNet()">
      </div>
    </div>
    <div class="form-row cols-2">
      <div class="fld">
        <label>Net Pay</label>
        <input type="number" name="net_pay" id="net_pay" value="{{ old('net_pay', $entry->net_pay ?? 0) }}" readonly style="background:var(--bg4);color:var(--green);font-weight:700;">
      </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <a href="{{ route('bms.payroll.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Add Entry</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function fillStaffName(sel) {
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('staff_name_hidden').value = opt.dataset.name || '';
  const sal = parseFloat(opt.dataset.salary || 0);
  if (sal) {
    document.getElementById('gross_salary').value = sal;
    calcNet();
  }
}
function calcNet() {
  const g = parseFloat(document.getElementById('gross_salary').value) || 0;
  const nhif = parseFloat(document.getElementById('nhif').value) || 0;
  const nssf = parseFloat(document.getElementById('nssf').value) || 0;
  const paye = parseFloat(document.getElementById('paye').value) || 0;
  document.getElementById('net_pay').value = (g - nhif - nssf - paye).toFixed(2);
}
</script>
@endpush
@endsection
