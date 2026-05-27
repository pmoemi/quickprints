@extends('emails._layout')

@section('content')
@php
  $inv     = $settings['invoice'] ?? [];
  $vatRate = (float)($settings['vat_rate'] ?? 16);
  $vatAmt  = $job->amount * $vatRate / (100 + $vatRate);
  $net     = $job->amount - $vatAmt;
@endphp

<h2>Invoice {{ $job->id }}</h2>
<p>{{ $tpl['greeting'] ?? 'Dear ' . ($client?->name ?? 'Valued Customer') . ',' }}</p>
<p>{{ $tpl['intro'] ?? 'Please find attached the invoice for your recent job. Thank you for your business.' }}</p>

<table class="info">
  <tr>
    <td class="lbl">Invoice No.</td>
    <td class="val" style="font-family:monospace;">{{ $job->id }}</td>
  </tr>
  <tr>
    <td class="lbl">Date</td>
    <td class="val">{{ now()->format('d M Y') }}</td>
  </tr>
  @if($client)
  <tr>
    <td class="lbl">Bill To</td>
    <td class="val">{{ $client->name }}@if($client->company)<br>{{ $client->company }}@endif</td>
  </tr>
  @endif
  <tr>
    <td class="lbl">Description</td>
    <td class="val">{{ $job->title }}</td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th>Description</th>
      <th class="num">Net (ex-VAT)</th>
      <th class="num">VAT ({{ $vatRate }}%)</th>
      <th class="num">Total</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{{ $job->title }}@if($job->notes)<br><small style="color:#64748b;">{{ $job->notes }}</small>@endif</td>
      <td class="num">{{ $symbol }} {{ number_format($net, 2) }}</td>
      <td class="num">{{ $symbol }} {{ number_format($vatAmt, 2) }}</td>
      <td class="num"><strong>{{ $symbol }} {{ number_format($job->amount, 2) }}</strong></td>
    </tr>
  </tbody>
</table>

<div style="text-align:right;" class="total-line">
  TOTAL DUE: {{ $symbol }} {{ number_format($job->amount, 2) }}
  &nbsp;
  @if($job->paid)
    <span class="badge badge-paid">PAID</span>
  @else
    <span class="badge badge-unpaid">UNPAID</span>
  @endif
</div>

@if(!empty($inv['mpesa_paybill']) || !empty($inv['terms_default']))
<hr>
@if(!empty($inv['mpesa_paybill']))
<p style="font-size:13px;"><strong>Payment via M-Pesa:</strong> Paybill <strong>{{ $inv['mpesa_paybill'] }}</strong>
  @if(!empty($inv['mpesa_account_hint'])) — Account: {{ $inv['mpesa_account_hint'] }}@endif
</p>
@endif
@if(!empty($inv['terms_default']))
<p style="font-size:12px;color:#64748b;">{!! nl2br(e($inv['terms_default'])) !!}</p>
@endif
@endif

<p>{{ $tpl['closing'] ?? 'Please contact us if you have any questions regarding this invoice.' }}</p>
@endsection
