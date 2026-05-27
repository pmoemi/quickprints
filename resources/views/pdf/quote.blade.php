@php
  use App\Support\DocTemplateEngine;
  $engine   = new DocTemplateEngine($settings);
  $inv      = $settings['invoice'] ?? [];
  $symbol   = $settings['currency_symbol'] ?? 'KSh';
  $vatRate  = (float)($quote->vat_rate ?? $settings['vat_rate'] ?? 16);
  $items    = $quote->items ?? [];
  $subtotal = collect($items)->sum(fn($i) => ($i['qty'] ?? 1) * ($i['unit_price'] ?? 0));
  $vatAmt   = $subtotal * $vatRate / 100;
  $total    = $subtotal + $vatAmt;
  $validity = $engine->get('quote_validity_days', 30);

  $statusBadge = '<span class="badge badge-' . ($quote->status ?? 'draft') . '">' . strtoupper($quote->status ?? 'DRAFT') . '</span>';
  $docRef = '#QT-' . str_pad($quote->id, 4, '0', STR_PAD_LEFT)
    . '<br>' . ($quote->date ? $quote->date->format('d M Y') : now()->format('d M Y'))
    . '<br>Valid: ' . $validity . ' days';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quotation #{{ $quote->id }}</title>
<style>{!! $engine->css() !!}</style>
</head>
<body>
<div class="doc-wrap">

  {!! $engine->header('QUOTATION', $docRef, $statusBadge) !!}

  {{-- Client / Quote Info --}}
  <div class="info-grid">
    <div class="info-col">
      <div class="info-title">Prepared For</div>
      <div class="info-value">
        <strong>{{ $quote->client_name ?? 'Client' }}</strong><br>
        @if(!empty($quote->client_phone)){{ $quote->client_phone }}<br>@endif
        @if(!empty($quote->branch))Branch: {{ $quote->branch }}@endif
      </div>
    </div>
    <div class="info-col">
      <div class="info-title">Quotation Info</div>
      <div class="info-value">
        <strong>Ref:</strong> QT-{{ str_pad($quote->id, 4, '0', STR_PAD_LEFT) }}<br>
        <strong>Date:</strong> {{ $quote->date ? $quote->date->format('d M Y') : now()->format('d M Y') }}<br>
        @if($quote->prepared_by)<strong>Prepared by:</strong> {{ $quote->prepared_by }}<br>@endif
        <strong>Valid:</strong> {{ $validity }} days from date
      </div>
    </div>
  </div>

  {{-- Line Items --}}
  <table>
    <thead>
      <tr>
        <th style="width:42%;">Description</th>
        <th class="text-right" style="width:8%;">Qty</th>
        <th class="text-right" style="width:20%;">Unit Price ({{ $symbol }})</th>
        <th class="text-right" style="width:10%;">Disc %</th>
        <th class="text-right" style="width:20%;">Total ({{ $symbol }})</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $item)
        @php
          $lineQty   = $item['qty'] ?? 1;
          $linePrice = $item['unit_price'] ?? 0;
          $lineDisc  = $item['discount'] ?? 0;
          $lineTotal = $lineQty * $linePrice * (1 - $lineDisc / 100);
        @endphp
        <tr>
          <td>
            <strong>{{ $item['description'] ?? '—' }}</strong>
            @if(!empty($item['notes']))<br><span style="font-size:11px;color:#888;">{{ $item['notes'] }}</span>@endif
          </td>
          <td class="text-right">{{ $lineQty }}</td>
          <td class="text-right mono">{{ number_format($linePrice, 2) }}</td>
          <td class="text-right">{{ $lineDisc > 0 ? $lineDisc.'%' : '—' }}</td>
          <td class="text-right mono">{{ number_format($lineTotal, 2) }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center" style="padding:20px;color:#999;">No items</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Totals --}}
  <div class="totals-wrap">
    <table class="totals-table">
      <tr><td style="color:#666;">Subtotal</td><td class="text-right mono">{{ $symbol }} {{ number_format($subtotal, 2) }}</td></tr>
      <tr><td style="color:#666;">VAT ({{ $vatRate }}%)</td><td class="text-right mono">{{ $symbol }} {{ number_format($vatAmt, 2) }}</td></tr>
      <tr class="total-final"><td>TOTAL</td><td class="text-right mono">{{ $symbol }} {{ number_format($total, 2) }}</td></tr>
    </table>
  </div>

  {{-- Notes --}}
  @if(!empty($quote->notes))
  <div class="pay-box" style="margin-top:20px;">
    <strong>Notes</strong>
    {!! nl2br(e($quote->notes)) !!}
  </div>
  @endif

  {{-- Terms --}}
  @if(!empty($inv['terms_default']))
  <div class="pay-box" style="margin-top:10px;">
    <strong>Terms &amp; Conditions</strong>
    {!! nl2br(e($inv['terms_default'])) !!}
  </div>
  @endif

  {!! $engine->footer($engine->get('quote_footer_text', 'This is a quotation, not a tax invoice.')) !!}
  {!! $engine->signature() !!}

</div>
</body>
</html>
