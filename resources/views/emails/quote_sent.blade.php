@extends('emails._layout')

@section('content')
<h2>Quotation QT-{{ str_pad($quote->id, 4, '0', STR_PAD_LEFT) }}</h2>
<p>{{ $tpl['greeting'] ?? 'Dear ' . ($quote->client_name ?? 'Valued Customer') . ',' }}</p>
<p>{{ $tpl['intro'] ?? 'Thank you for your interest in ' . ($settings['company_name'] ?? 'Quick Prints') . '. Please find your quotation details below. The full quotation PDF is also attached.' }}</p>

<table class="info">
  <tr>
    <td class="lbl">Quote No.</td>
    <td class="val" style="font-family:monospace;">QT-{{ str_pad($quote->id, 4, '0', STR_PAD_LEFT) }}</td>
  </tr>
  <tr>
    <td class="lbl">Date</td>
    <td class="val">{{ \Carbon\Carbon::parse($quote->date)->format('d M Y') }}</td>
  </tr>
  <tr>
    <td class="lbl">Prepared By</td>
    <td class="val">{{ $quote->prepared_by ?? ($settings['company_name'] ?? 'Quick Prints') }}</td>
  </tr>
  <tr>
    <td class="lbl">Branch</td>
    <td class="val">{{ $quote->branch }}</td>
  </tr>
</table>

@if(count($items) > 0)
<table class="items">
  <thead>
    <tr>
      <th>Description</th>
      <th class="num">Qty</th>
      <th class="num">Unit Price</th>
      <th class="num">Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
    @php $lineTotal = ($item['qty'] ?? 1) * ($item['unit_price'] ?? 0); @endphp
    <tr>
      <td>{{ $item['description'] ?? '—' }}</td>
      <td class="num">{{ $item['qty'] ?? 1 }}</td>
      <td class="num">{{ $symbol }} {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
      <td class="num">{{ $symbol }} {{ number_format($lineTotal, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div style="text-align:right;font-size:13px;color:#64748b;margin-top:8px;">
  Subtotal: {{ $symbol }} {{ number_format($subtotal, 2) }}<br>
  VAT ({{ $quote->vat_rate ?? 16 }}%): {{ $symbol }} {{ number_format($total - $subtotal, 2) }}
</div>
<div style="text-align:right;" class="total-line">
  TOTAL: {{ $symbol }} {{ number_format($total, 2) }}
</div>

@if($quote->notes)
<hr>
<p style="font-size:13px;"><strong>Notes:</strong> {{ $quote->notes }}</p>
@endif

<hr>
<p>{{ $tpl['validity_note'] ?? 'This quotation is valid for ' . ($settings['invoice']['quote_validity_days'] ?? 30) . ' days. To proceed or ask any questions, please contact us.' }}</p>
@if(!empty($settings['phone']))<p style="font-size:13px;">{{ $settings['phone'] }}</p>@endif
@if(!empty($settings['email']))<p style="font-size:13px;">{{ $settings['email'] }}</p>@endif
@endsection
