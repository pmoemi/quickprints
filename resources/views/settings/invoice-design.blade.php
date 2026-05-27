@php
  $inv    = $settings['invoice'] ?? [];
  $accent = $inv['accent_color'] ?? '#f97316';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Template Designer — {{ $settings['company_name'] ?? 'QuickPrints' }}</title>
<style>
/* ── Reset ─────────────────────────────────── */
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;overflow:hidden;}

/* ── CSS Variables (dark) ───────────────────── */
:root{
  --bg0:#0a0f1e;
  --bg1:#0f172a;
  --bg2:#1e293b;
  --bg3:#2d3748;
  --border:#334155;
  --border2:#475569;
  --text1:#f1f5f9;
  --text2:#94a3b8;
  --text3:#64748b;
  --accent:{{ $accent }};
  --radius:6px;
  --green:#16a34a;
  --red:#dc2626;
  --green-dim:rgba(22,163,74,.12);
  --red-dim:rgba(220,38,38,.12);
}
body{font-family:'Inter','Segoe UI',Arial,sans-serif;background:var(--bg0);color:var(--text1);font-size:13px;display:flex;flex-direction:column;}

/* ── Topbar ─────────────────────────────────── */
#topbar{
  height:52px;background:var(--bg1);border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:10px;padding:0 16px;flex-shrink:0;
}
.tb-back{
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 12px;border-radius:var(--radius);
  background:var(--bg3);color:var(--text2);
  text-decoration:none;font-size:12px;font-weight:600;
  border:1px solid var(--border);white-space:nowrap;
  transition:color .15s;
}
.tb-back:hover{color:var(--text1);}
.tb-title{font-weight:700;font-size:14px;color:var(--text1);white-space:nowrap;}
.tb-sep{width:1px;height:24px;background:var(--border);flex-shrink:0;}
.doc-tabs{display:flex;gap:3px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:3px;}
.doc-tab{
  padding:5px 14px;border-radius:4px;font-size:12px;font-weight:600;
  border:none;cursor:pointer;background:transparent;color:var(--text3);transition:all .15s;
}
.doc-tab.active{background:var(--accent);color:#fff;}
.tb-spacer{flex:1;}
.flash-ok{font-size:12px;font-weight:600;color:var(--green);white-space:nowrap;}
.flash-err{font-size:12px;color:var(--red);white-space:nowrap;}
.tb-save{
  padding:7px 22px;background:var(--accent);color:#fff;
  border:none;border-radius:var(--radius);font-size:13px;font-weight:700;
  cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;
}
.tb-save:hover{opacity:.9;}

/* ── Split panel ────────────────────────────── */
#main-panel{display:flex;flex:1;overflow:hidden;}

/* ── Form panel (left) ──────────────────────── */
#form-panel{
  width:400px;min-width:300px;overflow-y:auto;
  background:var(--bg0);border-right:1px solid var(--border);
  flex-shrink:0;padding:14px;
}
.btn-form-save{
  width:100%;padding:10px;background:var(--accent);color:#fff;
  border:none;border-radius:var(--radius);font-size:13px;font-weight:700;
  cursor:pointer;margin-top:4px;
}
.btn-form-save:hover{opacity:.9;}

/* ── Preview panel (right) ──────────────────── */
#preview-panel{flex:1;display:flex;flex-direction:column;background:#cbd5e1;min-width:0;overflow:hidden;}
#preview-head{
  background:var(--bg2);border-bottom:1px solid var(--border);
  padding:8px 16px;display:flex;align-items:center;gap:8px;flex-shrink:0;
}
#preview-head span{font-size:11px;font-weight:600;color:var(--text3);}
#preview-frame{width:100%;flex:1;border:none;display:block;background:#fff;}

/* ── Mobile: stacked layout ─────────────────── */
@media(max-width:768px){
  html,body{overflow:auto;}
  #topbar{height:auto;flex-wrap:wrap;padding:8px 10px;gap:6px;}
  .tb-title{font-size:13px;}
  .tb-back{padding:4px 8px;font-size:11px;}
  .doc-tabs .doc-tab{padding:4px 10px;font-size:11px;}
  .tb-save{padding:6px 14px;font-size:12px;}
  #main-panel{flex-direction:column;overflow:visible;}
  #form-panel{
    width:100%;min-width:0;
    border-right:none;border-bottom:1px solid var(--border);
    overflow-y:visible;
    max-height:none;
  }
  #preview-panel{
    height:420px;
    flex-shrink:0;
  }
  #preview-frame{height:100%;}
}

/* ── Form component classes (mirror BMS) ────── */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:12px;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--border);}
.card-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text2);}
.form-row{padding:10px 14px;}
.form-row+.form-row{border-top:1px solid var(--border);}
.form-row.cols-2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.fld{display:flex;flex-direction:column;gap:4px;}
label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);display:block;}
input[type=text],input[type=number],input[type=email],select,textarea{
  width:100%;background:var(--bg3);border:1px solid var(--border);
  border-radius:var(--radius);color:var(--text1);font-size:13px;
  padding:7px 10px;outline:none;transition:border-color .15s;font-family:inherit;
}
input[type=text]:focus,input[type=number]:focus,select:focus,textarea:focus{border-color:var(--accent);}
input[type=checkbox]{accent-color:var(--accent);width:14px;height:14px;cursor:pointer;}
textarea{resize:vertical;}
select option{background:var(--bg2);color:var(--text1);}
</style>
</head>
<body>

{{-- ── Topbar ──────────────────────────────── --}}
<div id="topbar">
  <a href="{{ route('bms.settings.invoice') }}" class="tb-back">&#8592; Settings</a>
  <span class="tb-sep"></span>
  <span class="tb-title">Template Designer</span>
  <div class="doc-tabs">
    <button type="button" class="doc-tab active" id="tab-invoice" onclick="setPreviewDoc('invoice')">Invoice</button>
    <button type="button" class="doc-tab" id="tab-quote" onclick="setPreviewDoc('quote')">Quote</button>
    <button type="button" class="doc-tab" id="tab-receipt" onclick="setPreviewDoc('receipt')">Receipt</button>
  </div>
  <div class="tb-spacer"></div>
  @if(session('success'))<span class="flash-ok">&#10003; {{ session('success') }}</span>@endif
  @if(session('error'))<span class="flash-err">{{ session('error') }}</span>@endif
  @if($errors->any())<span class="flash-err">{{ $errors->first() }}</span>@endif
  <button type="submit" form="inv-form" class="tb-save">&#10003; Save</button>
</div>

{{-- ── Split panel ──────────────────────────── --}}
<div id="main-panel">

  {{-- Left: scrollable form --}}
  <div id="form-panel">
    <form id="inv-form" method="POST" action="{{ route('bms.settings.invoice.update') }}">
      @csrf
      @method('PUT')
      @include('settings._invoice_form')
      <button type="submit" class="btn-form-save">Save Template Settings</button>
    </form>
  </div>

  {{-- Right: live preview --}}
  <div id="preview-panel">
    <div id="preview-head">
      <span>Live Preview</span>
      <span style="margin-left:auto;font-size:10px;">Updates automatically &bull; reflects PDF output</span>
    </div>
    <iframe id="preview-frame"
      srcdoc="<div style='font-family:sans-serif;color:#888;padding:60px;text-align:center;font-size:14px;'>Loading preview…</div>">
    </iframe>
  </div>

</div>

<script>
(function () {
  const form      = document.getElementById('inv-form');
  const frame     = document.getElementById('preview-frame');
  const csrfToken = document.querySelector('meta[name=csrf-token]').content;
  let   docType   = 'invoice';
  let   timer;

  // Color picker ↔ text input sync
  window.syncColor = function (textInput, colorName) {
    const val = textInput.value.trim();
    const picker = form.elements[colorName];
    if (picker && /^#[0-9a-fA-F]{3,8}$/.test(val)) picker.value = val;
    scheduleUpdate();
  };

  form.querySelectorAll('input[type=color]').forEach(picker => {
    picker.addEventListener('input', function () {
      const txt = form.elements[this.name + '_txt'];
      if (txt) txt.value = this.value;
      scheduleUpdate();
    });
  });

  form.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('change', scheduleUpdate);
    el.addEventListener('input',  scheduleUpdate);
  });

  function scheduleUpdate() {
    clearTimeout(timer);
    timer = setTimeout(updatePreview, 500);
  }

  // Doc type tab switching
  window.setPreviewDoc = function (type) {
    docType = type;
    ['invoice', 'quote', 'receipt'].forEach(t => {
      const tab = document.getElementById('tab-' + t);
      if (tab) tab.classList.toggle('active', t === type);
    });
    updatePreview();
  };

  // Margin preset helper (called by onclick in _invoice_form partial)
  window.applyMarginPreset = function (val) {
    ['margin_top', 'margin_right', 'margin_bottom', 'margin_left'].forEach(function (id) {
      const el = document.getElementById(id);
      if (el) { el.value = val; el.dispatchEvent(new Event('input')); }
    });
  };

  // Tagline style toggle (called by onchange in _invoice_form partial)
  window.toggleTaglineStyle = function (style) {
    const show = style === 'colored';
    const dividerRow  = document.getElementById('tl-divider-row');
    const colorRows   = document.getElementById('tl-color-rows');
    if (dividerRow) dividerRow.style.display = show ? '' : 'none';
    if (colorRows)  colorRows.style.display  = show ? 'grid' : 'none';
    scheduleUpdate();
  };

  // Watermark type toggle (called by onchange in _invoice_form partial)
  window.toggleWatermarkType = function (type) {
    const textRow    = document.getElementById('wm-text-row');
    const imageRow   = document.getElementById('wm-image-row');
    const opacityEl  = document.getElementById('wm-opacity');
    const rotationEl = document.getElementById('wm-rotation');
    if (textRow)    textRow.style.display   = type === 'text'  ? '' : 'none';
    if (imageRow)   imageRow.style.display  = type === 'image' ? '' : 'none';
    const dim = type === 'none' ? '0.4' : '1';
    if (opacityEl)  opacityEl.style.opacity  = dim;
    if (rotationEl) rotationEl.style.opacity = dim;
    scheduleUpdate();
  };

  // Watermark image AJAX upload
  const wmFileInput = document.getElementById('wm-image-file');
  if (wmFileInput) {
    wmFileInput.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const status = document.getElementById('wm-upload-status');
      status.textContent = 'Uploading…';
      const fd = new FormData();
      fd.append('watermark_image', file);
      fetch('{{ route("bms.settings.invoice.watermark-image") }}', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body:    fd,
      })
      .then(r => r.json())
      .then(data => {
        if (!data.url) throw new Error('No URL returned');
        document.getElementById('wm-image-url').value = data.url;
        const prev = document.getElementById('wm-preview-img');
        if (prev) { prev.src = data.url; prev.style.display = ''; }
        status.textContent = '✓ Uploaded';
        status.style.color = '#16a34a';
        scheduleUpdate();
      })
      .catch(() => {
        status.textContent = 'Upload failed';
        status.style.color = '#dc2626';
      });
    });
  }

  // Fetch live preview
  function updatePreview() {
    const data = new FormData(form);
    data.delete('_method');           // drop PUT spoof so the POST route matches
    data.append('_preview_doc', docType);

    fetch('{{ route("bms.settings.invoice.preview") }}', {
      method:  'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'text/html' },
      body:    data,
    })
    .then(r => {
      if (!r.ok) return r.text().then(t => Promise.reject({ status: r.status, body: t }));
      return r.text();
    })
    .then(html => { frame.srcdoc = html; })
    .catch(err => {
      const msg = (typeof err === 'object' && err.body) ? err.body : String(err);
      frame.srcdoc = '<div style="font-family:sans-serif;padding:32px;color:#dc2626;font-size:12px;white-space:pre-wrap;">'
        + msg.replace(/</g, '&lt;') + '</div>';
    });
  }

  // Initial load
  updatePreview();
})();
</script>
</body>
</html>
