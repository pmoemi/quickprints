@php
  use App\Support\DocTemplateEngine;
  $engine  = new DocTemplateEngine($settings);
  $symbol  = $settings['currency_symbol'] ?? 'KSh';
  $vatRate = (float)($settings['vat_rate'] ?? 16);
  $vatAmt  = $job->amount * $vatRate / (100 + $vatRate);
  $net     = $job->amount - $vatAmt;

  $rcpPrefix = $settings['numbering']['receipt_prefix'] ?? 'RCP';
  $docRef  = e($rcpPrefix . '-' . $job->id) . '<br>' . now()->format('d M Y');
  $docMeta = '<div class="receipt-stamp">PAID</div>';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt {{ $job->id }}</title>
<style>{!! $engine->css() !!}</style>
</head>
<body>
<div class="doc-wrap">

  {!! $engine->header('PAYMENT RECEIPT', $docRef, $docMeta) !!}

  {{-- Received From / Payment Info --}}
  <div class="info-grid">
    <div class="info-col">
      <div class="info-title">Received From</div>
      <div class="info-value">
        <strong>{{ $client?->name ?? 'Walk-in Client' }}</strong><br>
        @if($client?->company){{ $client->company }}<br>@endif
        @if($client?->phone){{ $client->phone }}<br>@endif
        @if($client?->email){{ $client->email }}@endif
      </div>
    </div>
    <div class="info-col">
      <div class="info-title">Payment Info</div>
      <div class="info-value">
        <strong>Job:</strong> {{ $job->id }}<br>
        <strong>Branch:</strong> {{ $job->branch }}<br>
        @if($job->category)<strong>Category:</strong> {{ $job->category }}<br>@endif
        <strong>Date:</strong> {{ now()->format('d M Y') }}
      </div>
    </div>
  </div>

  {{-- Items --}}
  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="text-right">Net Amount ({{ $symbol }})</th>
        <th class="text-right">VAT {{ $vatRate }}% ({{ $symbol }})</th>
        <th class="text-right">Total Paid ({{ $symbol }})</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <strong>{{ $job->title }}</strong>
          @if($job->category)<br><span style="font-size:11px;color:#888;">{{ $job->category }}</span>@endif
        </td>
        <td class="text-right mono">{{ number_format($net, 2) }}</td>
        <td class="text-right mono">{{ number_format($vatAmt, 2) }}</td>
        <td class="text-right mono" style="font-weight:700;">{{ number_format($job->amount, 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals-wrap">
    <table class="totals-table">
      <tr><td style="color:#666;">Amount Received</td><td class="text-right mono">{{ $symbol }} {{ number_format($job->amountPaid(), 2) }}</td></tr>
      <tr><td style="color:#666;">Balance Due</td><td class="text-right mono">{{ $symbol }} {{ number_format($job->balanceDue(), 2) }}</td></tr>
      <tr class="total-final"><td>TOTAL PAID</td><td class="text-right mono">{{ $symbol }} {{ number_format($job->amountPaid(), 2) }}</td></tr>
    </table>
  </div>

  <div style="margin-top:24px;padding:14px;background:#f0fdf4;border-radius:6px;font-size:12px;color:#166534;text-align:center;">
    Payment confirmed. Thank you for your business!
  </div>

  {!! $engine->footer() !!}
  {!! $engine->signature() !!}

</div>
</body>
</html>
