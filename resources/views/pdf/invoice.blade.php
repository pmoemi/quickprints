@php
  use App\Support\DocTemplateEngine;
  $engine   = new DocTemplateEngine($settings);
  $inv      = $settings['invoice'] ?? [];
  $symbol   = $settings['currency_symbol'] ?? 'KSh';
  $vatRate  = (float)($settings['vat_rate'] ?? 16);
  $vatAmt   = $job->amount * $vatRate / (100 + $vatRate);
  $net      = $job->amount - $vatAmt;
  $showVat  = $engine->get('show_vat_column', true);
  $isFormal = $engine->layout() === 'formal';

  $invPrefix = $settings['numbering']['invoice_prefix'] ?? 'INV';
  $docRef = e($invPrefix . '-' . $job->id)
    . '<br>' . now()->format('d M Y')
    . ($job->deadline ? '<br>Due: ' . $job->deadline->format('d M Y') : '');
  $docMeta = \App\Support\JobPaymentBadge::invoiceMeta($job);

  $artworkUrl = $job->artwork_url ?? '';
  // Convert to data URI so dompdf can embed the image without HTTP requests
  $artworkSrc = $artworkUrl ? DocTemplateEngine::logoDataUri($artworkUrl) : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $job->id }}</title>
<style>{!! $engine->css() !!}</style>
</head>
<body>
{!! $engine->watermarkOverlay() !!}
<div class="doc-wrap">

@if($isFormal)
{{-- ── FORMAL LAYOUT ──────────────────────────────────────────── --}}
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
          @if($artworkSrc)
            <div class="fml-item-artwork">
              <img src="{{ $artworkSrc }}" alt="Job Artwork">
            </div>
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
{{-- ── CLASSIC / MODERN / MINIMAL LAYOUTS ────────────────────── --}}
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
        <strong>Branch:</strong> {{ $job->branch }}<br>
        @if($job->category)<strong>Category:</strong> {{ $job->category }}<br>@endif
        @if($job->deadline)<strong>Deadline:</strong> {{ $job->deadline->format('d M Y') }}<br>@endif
        @if($job->sales_rep_id)<strong>Sales Rep:</strong> {{ optional($job->salesRep ?? null)->name ?? $job->sales_rep_id }}@endif
      </div>
    </div>
  </div>

  @if($artworkSrc)
    {!! $engine->artworkHtml($artworkSrc, 'Job Artwork') !!}
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
          @if($job->notes)<br><span style="font-size:11px;color:#888;">{{ $job->notes }}</span>@endif
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
        <tr><td style="color:#666;">Subtotal (ex-VAT)</td><td class="text-right mono">{{ $symbol }} {{ number_format($net, 2) }}</td></tr>
        <tr><td style="color:#666;">VAT ({{ $vatRate }}%)</td><td class="text-right mono">{{ $symbol }} {{ number_format($vatAmt, 2) }}</td></tr>
      @endif
      <tr class="total-final"><td>TOTAL DUE</td><td class="text-right mono">{{ $symbol }} {{ number_format($job->amount, 2) }}</td></tr>
    </table>
  </div>

  @if(!empty($inv['mpesa_paybill']) || !empty($inv['terms_default']))
  <div class="pay-box">
    @if(!empty($inv['mpesa_paybill']))
      <strong>Payment Details</strong>
      M-Pesa Paybill: <strong>{{ $inv['mpesa_paybill'] }}</strong>
      @if(!empty($inv['mpesa_account_hint'])) &nbsp;·&nbsp; Account: {{ $inv['mpesa_account_hint'] }}@endif
      <br>
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
</body>
</html>
