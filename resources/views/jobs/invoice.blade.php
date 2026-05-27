@php
  use App\Support\DocTemplateEngine;
  $engine  = new DocTemplateEngine($settings);
  $inv     = $settings['invoice'] ?? [];
  $symbol  = $settings['currency_symbol'] ?? 'KSh';
  $vatRate = (float)($settings['vat_rate'] ?? 16);
  $vatAmt  = $job->amount * $vatRate / (100 + $vatRate);
  $net     = $job->amount - $vatAmt;
  $showVat = $engine->get('show_vat_column', true);
  $accent  = $engine->accent();

  $docRef  = e($job->id)
    . '<br>' . now()->format('d M Y')
    . ($job->deadline ? '<br>Due: ' . $job->deadline->format('d M Y') : '');
  $docMeta = $job->paid
    ? '<span class="badge badge-paid">PAID</span>'
    : '<span class="badge badge-unpaid">UNPAID</span>';

  $artworkUrl = $job->artwork_url ?? '';
  $isFormal   = $engine->layout() === 'formal';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $job->id }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'IBM Plex Sans',Arial,sans-serif;background:#f1f5f9;color:#111;}
.toolbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:10px 20px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;position:sticky;top:0;z-index:10;}
.toolbar-title{font-size:13px;font-weight:600;color:#64748b;margin-right:auto;}
.tbtn{padding:7px 14px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
.tbtn-primary{background:{{ $accent }};color:#fff;}
.tbtn-green{background:#16a34a;color:#fff;}
.tbtn-blue{background:#0369a1;color:#fff;}
.tbtn-amber{background:#d97706;color:#fff;}
.tbtn-neutral{background:#f1f5f9;color:#374151;border:1px solid #d1d5db;}
.tbtn-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
.flash-ok{color:#16a34a;font-size:12px;font-weight:600;}
.flash-err{color:#dc2626;font-size:12px;}
.doc-outer{max-width:800px;margin:28px auto;background:#fff;border-radius:10px;box-shadow:0 4px 28px rgba(0,0,0,.09);overflow:hidden;}
.doc-inner{padding:{{ $engine->marginShorthand() }};}
{!! $engine->css(true) !!}
.doc-header{margin-bottom:0;}

/* Artwork panel */
.artwork-panel{max-width:800px;margin:0 auto 0;background:#fffbf5;border:2px dashed #fed7aa;border-radius:10px;padding:16px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.artwork-panel img{max-height:80px;max-width:180px;object-fit:contain;border-radius:6px;border:1px solid #fed7aa;}
.artwork-upload-zone{flex:1;min-width:200px;}
.artwork-upload-zone p{font-size:12px;color:#92400e;margin-bottom:6px;font-weight:500;}
.artwork-file-input{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.artwork-file-input input[type=file]{font-size:12px;color:#374151;flex:1;}
</style>
</head>
<body>

<div class="toolbar">
  <span class="toolbar-title">Invoice {{ $job->id }}</span>
  @if(session('success'))<span class="flash-ok">&#10003; {{ session('success') }}</span>@endif
  @if(session('error'))<span class="flash-err">{{ session('error') }}</span>@endif

  <a href="{{ route('bms.jobs.invoice.pdf', $job->id) }}" class="tbtn tbtn-primary">&#8659; PDF Invoice</a>
  @if($job->paid)
    <a href="{{ route('bms.jobs.receipt.pdf', $job->id) }}" class="tbtn tbtn-green">&#8659; PDF Receipt</a>
  @endif
  @if($client?->email)
    <form method="POST" action="{{ route('bms.jobs.invoice.send', $job->id) }}" style="display:inline;">
      @csrf
      <button type="submit" class="tbtn tbtn-blue">&#9993; Email Invoice</button>
    </form>
  @endif
  <button onclick="toggleArtworkPanel()" class="tbtn tbtn-amber" id="btn-artwork">
    &#128444; {{ $artworkUrl ? 'Replace Artwork' : 'Upload Artwork' }}
  </button>
  <button onclick="window.print()" class="tbtn tbtn-neutral">&#128438; Print</button>
  <a href="{{ url()->previous() }}" class="tbtn tbtn-neutral">&#8592; Back</a>
</div>

{{-- Artwork panel (collapsible) --}}
<div id="artwork-panel" class="artwork-panel" style="{{ $artworkUrl ? '' : 'display:none;' }} margin-top:12px;">
  @if($artworkUrl)
    <img src="{{ $artworkUrl }}" id="artwork-preview" alt="Job Artwork">
  @else
    <img src="" id="artwork-preview" alt="" style="display:none;">
  @endif
  <div class="artwork-upload-zone">
    <p>&#128444; Attach artwork to embed in this invoice and PDF. Supports JPG, PNG, WebP (max 8 MB).</p>
    <div class="artwork-file-input">
      <input type="file" id="artwork-file" accept="image/jpeg,image/png,image/webp,image/gif">
      <button type="button" id="artwork-upload-btn" onclick="uploadArtwork()"
        style="padding:6px 14px;background:#d97706;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
        Upload
      </button>
      @if($artworkUrl)
      <button type="button" onclick="removeArtwork()"
        style="padding:6px 12px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
        Remove
      </button>
      @endif
    </div>
    <div id="artwork-status" style="font-size:11px;color:#92400e;margin-top:6px;"></div>
  </div>
</div>

{{-- Document --}}
<div class="doc-outer">
  <div class="doc-inner">

    {!! $engine->watermarkOverlay() !!}

    @if($isFormal)
    {{-- ── FORMAL LAYOUT ──────────────────────────────────────── --}}
    {!! $engine->formalHeader('INVOICE', e($job->id), now()->format('d M Y'), $docMeta) !!}

    <div class="fml-bill-row">
      <div class="fml-bill-cell">
        <div class="fml-bill-box">
          <div class="fml-bill-lbl">BILL TO</div>
          <div class="fml-bill-body">
            {{ $client?->name ?? 'Walk-in Client' }}
            @if($client?->company)<br>{{ $client->company }}@endif
            @if($client?->phone)<br>{{ $client->phone }}@endif
            @if($client?->email)<br>{{ $client->email }}@endif
          </div>
        </div>
      </div>
      <div class="fml-proj-cell">
        <table class="fml-proj">
          <thead><tr><th>CATEGORY</th><th>BRANCH</th><th>DEADLINE</th></tr></thead>
          <tbody><tr>
            <td>{{ $job->category ?? '—' }}</td>
            <td>{{ $job->branch ?? '—' }}</td>
            <td>{{ $job->deadline?->format('d M Y') ?? '—' }}</td>
          </tr></tbody>
        </table>
      </div>
    </div>

    <table class="fml-items">
      <thead>
        <tr>
          <th style="width:7mm;">#</th>
          <th>DESCRIPTION</th>
          <th style="width:12mm;">QTY</th>
          <th style="width:18mm;">UNIT</th>
          @if($showVat)
            <th style="width:16mm;">VAT %</th>
            <th style="width:28mm;">EX-VAT ({{ $symbol }})</th>
          @endif
          <th style="width:28mm;">AMOUNT ({{ $symbol }})</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="fi-n">1</td>
          <td>
            <div class="fml-item-title">{{ $job->title }}</div>
            @if($artworkUrl)
              <div class="fml-item-artwork" id="doc-artwork">
                <img src="{{ $artworkUrl }}" alt="Job Artwork">
              </div>
            @else
              <div id="doc-artwork"></div>
            @endif
            @if($job->notes)
              <div class="fml-item-spec">{{ $job->notes }}</div>
            @endif
          </td>
          <td class="fi-q">1</td>
          <td class="fi-u">Job</td>
          @if($showVat)
            <td class="fi-v">{{ $vatRate }}%</td>
            <td class="fi-a">{{ number_format($net, 2) }}</td>
          @endif
          <td class="fi-a">{{ number_format($job->amount, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="fml-footer">
      <div class="fml-fl">
        <div class="fml-vat-head">VAT SUMMARY</div>
        <div class="fml-vat-body">
          @if($showVat)
          <table class="fml-vat">
            <thead><tr><th>DESCRIPTION</th><th>BASE</th><th>VAT %</th><th>VAT AMT</th></tr></thead>
            <tbody><tr>
              <td>Standard Rate</td>
              <td>{{ number_format($net, 2) }}</td>
              <td>{{ $vatRate }}%</td>
              <td>{{ number_format($vatAmt, 2) }}</td>
            </tr></tbody>
          </table>
          @endif
        </div>
        @if(!empty($inv['terms_default']))
          <div class="fml-terms-head">TERMS &amp; CONDITIONS</div>
          <div class="fml-terms-body">{!! nl2br(e($inv['terms_default'])) !!}</div>
        @endif
        @if(!empty($inv['mpesa_paybill']))
          <div style="padding:6px 10px;font-size:10px;border-top:1px solid #111;">
            <strong>M-Pesa Paybill: {{ $inv['mpesa_paybill'] }}</strong>
            @if(!empty($inv['mpesa_account_hint'])) &nbsp;·&nbsp; Account: {{ $inv['mpesa_account_hint'] }}@endif
          </div>
        @endif
      </div>
      <div class="fml-fr">
        @if($showVat)
          <div class="fml-tot"><div class="fml-tot-k">Subtotal</div><div class="fml-tot-v">{{ $symbol }}&nbsp;{{ number_format($net, 2) }}</div></div>
          <div class="fml-tot"><div class="fml-tot-k">VAT ({{ $vatRate }}%)</div><div class="fml-tot-v">{{ $symbol }}&nbsp;{{ number_format($vatAmt, 2) }}</div></div>
        @endif
        <div class="fml-tot grand"><div class="fml-tot-k">TOTAL</div><div class="fml-tot-v">{{ $symbol }}&nbsp;{{ number_format($job->amount, 2) }}</div></div>
      </div>
    </div>

    {!! $engine->signature() !!}
    {!! $engine->footer() !!}

    @else
    {{-- ── CLASSIC / MODERN / MINIMAL LAYOUTS ─────────────────── --}}
    {!! $engine->header('INVOICE', $docRef, $docMeta) !!}

    <div class="info-grid">
      <div class="info-col">
        <div class="info-title">Bill To</div>
        <div class="info-value">
          <strong>{{ $client?->name ?? 'Walk-in Client' }}</strong><br>
          @if($client?->company){{ $client->company }}<br>@endif
          @if($client?->phone){{ $client->phone }}<br>@endif
          @if($client?->email){{ $client->email }}@endif
        </div>
      </div>
      <div class="info-col">
        <div class="info-title">Job Details</div>
        <div class="info-value">
          @if($job->branch)<span style="color:#888;font-size:11px;">Branch</span><br>{{ $job->branch }}<br>@endif
          @if($job->category)<span style="color:#888;font-size:11px;">Category</span><br>{{ $job->category }}<br>@endif
          @if($job->deadline)<span style="color:#888;font-size:11px;">Deadline</span><br>{{ $job->deadline->format('d M Y') }}@endif
        </div>
      </div>
    </div>

    @if($artworkUrl)
      <div id="doc-artwork" style="margin:16px 0;text-align:center;">
        <img src="{{ $artworkUrl }}" alt="Job Artwork"
          style="max-width:100%;max-height:220px;object-fit:contain;border:1px solid #e5e7eb;border-radius:4px;">
        <div style="font-size:10px;color:#aaa;margin-top:4px;text-transform:uppercase;letter-spacing:.05em;">Artwork / Design Reference</div>
      </div>
    @else
      <div id="doc-artwork"></div>
    @endif

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th class="text-right">Qty</th>
          @if($showVat)<th class="text-right">Ex-VAT ({{ $symbol }})</th>@endif
          <th class="text-right">Amount ({{ $symbol }})</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>{{ $job->title }}</strong>
            @if($job->notes)<br><small style="color:#888;">{{ $job->notes }}</small>@endif
          </td>
          <td class="text-right">1</td>
          @if($showVat)<td class="text-right mono">{{ number_format($net, 2) }}</td>@endif
          <td class="text-right mono">{{ number_format($job->amount, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="totals-wrap">
      <table class="totals-table">
        @if($showVat)
          <tr><td style="color:#888;">Subtotal (ex-VAT)</td><td class="text-right mono">{{ $symbol }} {{ number_format($net, 2) }}</td></tr>
          <tr><td style="color:#888;">VAT ({{ $vatRate }}%)</td><td class="text-right mono">{{ $symbol }} {{ number_format($vatAmt, 2) }}</td></tr>
        @endif
        <tr class="total-final"><td>TOTAL DUE</td><td class="text-right mono">{{ $symbol }} {{ number_format($job->amount, 2) }}</td></tr>
      </table>
    </div>

    @if(!empty($inv['mpesa_paybill']) || !empty($inv['terms_default']))
    <div class="pay-box">
      @if(!empty($inv['mpesa_paybill']))
        <strong>Payment Details</strong>
        M-Pesa Paybill: <strong>{{ $inv['mpesa_paybill'] }}</strong>
        @if(!empty($inv['mpesa_account_hint'])) &nbsp;·&nbsp; Account: {{ $inv['mpesa_account_hint'] }}@endif<br>
      @endif
      @if(!empty($inv['terms_default']))
        <strong>Terms &amp; Notes</strong>
        {!! nl2br(e($inv['terms_default'])) !!}
      @endif
    </div>
    @endif

    {!! $engine->footer() !!}
    {!! $engine->signature() !!}

    @endif

  </div>
</div>

<script>
(function () {
  const csrf     = document.querySelector('meta[name=csrf-token]').content;
  const isFormal = {{ $isFormal ? 'true' : 'false' }};

  window.toggleArtworkPanel = function () {
    const panel = document.getElementById('artwork-panel');
    panel.style.display = panel.style.display === 'none' ? '' : 'none';
  };

  window.uploadArtwork = function () {
    const file = document.getElementById('artwork-file').files[0];
    if (!file) { setStatus('Please choose an image file first.', 'red'); return; }
    const fd = new FormData();
    fd.append('artwork', file);
    fd.append('_token', csrf);
    setStatus('Uploading…', '#92400e');
    fetch('{{ route("bms.jobs.artwork.upload", $job->id) }}', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.url) throw new Error('No URL returned');
        const prev = document.getElementById('artwork-preview');
        prev.src = data.url;
        prev.style.display = '';
        const docArt = document.getElementById('doc-artwork');
        if (isFormal) {
          docArt.className = 'fml-item-artwork';
          docArt.innerHTML = '<img src="' + data.url + '" alt="Job Artwork">';
        } else {
          docArt.innerHTML = '<img src="' + data.url + '" alt="Job Artwork" style="max-width:100%;max-height:220px;object-fit:contain;border:1px solid #e5e7eb;border-radius:4px;">'
            + '<div style="font-size:10px;color:#aaa;margin-top:4px;text-transform:uppercase;letter-spacing:.05em;">Artwork / Design Reference</div>';
        }
        document.getElementById('btn-artwork').textContent = '🖼 Replace Artwork';
        setStatus('Artwork uploaded. Reload to see it in PDF.', '#16a34a');
      })
      .catch(() => setStatus('Upload failed. Try again.', 'red'));
  };

  window.removeArtwork = function () {
    if (!confirm('Remove artwork from this invoice?')) return;
    fetch('{{ route("bms.jobs.artwork.remove", $job->id) }}', {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(() => {
      document.getElementById('artwork-preview').style.display = 'none';
      const docArt = document.getElementById('doc-artwork');
      docArt.className = '';
      docArt.innerHTML = '';
      document.getElementById('btn-artwork').textContent = '🖼 Upload Artwork';
      setStatus('Artwork removed.', '#64748b');
    });
  };

  function setStatus(msg, color) {
    const el = document.getElementById('artwork-status');
    el.textContent = msg;
    el.style.color = color;
  }
})();
</script>
</body>
</html>
