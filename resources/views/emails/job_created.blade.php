@extends('emails._layout')

@section('content')
<h2>Your job has been received!</h2>
<p>{{ $tpl['greeting'] ?? 'Hi ' . ($client?->name ?? 'Valued Customer') . ',' }}</p>
<p>{{ $tpl['intro'] ?? 'Thank you for choosing ' . ($settings['company_name'] ?? 'Quick Prints') . '. We have received your job and our team will get started shortly.' }}</p>

<table class="info">
  <tr>
    <td class="lbl">Job ID</td>
    <td class="val" style="font-family:monospace;">{{ $job->id }}</td>
  </tr>
  <tr>
    <td class="lbl">Description</td>
    <td class="val">{{ $job->title }}</td>
  </tr>
  @if($job->category)
  <tr>
    <td class="lbl">Category</td>
    <td class="val">{{ $job->category }}</td>
  </tr>
  @endif
  <tr>
    <td class="lbl">Branch</td>
    <td class="val">{{ $job->branch }}</td>
  </tr>
  @if($job->deadline)
  <tr>
    <td class="lbl">Deadline</td>
    <td class="val">{{ $job->deadline->format('d M Y') }}</td>
  </tr>
  @endif
  <tr>
    <td class="lbl">Amount</td>
    <td class="val">{{ $symbol }} {{ number_format($job->amount, 2) }}</td>
  </tr>
  <tr>
    <td class="lbl">Payment Status</td>
    <td class="val">
      @if($job->paid)
        <span class="badge badge-paid">PAID</span>
      @else
        <span class="badge badge-unpaid">PENDING PAYMENT</span>
      @endif
    </td>
  </tr>
</table>

@if($job->notes)
<div class="note"><strong>Notes:</strong> {{ $job->notes }}</div>
@endif

<hr>

@php $inv = $settings['invoice'] ?? []; @endphp
@if(!empty($inv['mpesa_paybill']))
<p style="font-size:13px;"><strong>Payment:</strong> M-Pesa Paybill <strong>{{ $inv['mpesa_paybill'] }}</strong>
  @if(!empty($inv['mpesa_account_hint'])) — Account: {{ $inv['mpesa_account_hint'] }}@endif
</p>
@endif

<p>{{ $tpl['closing'] ?? 'We will notify you as your job progresses. If you have any questions, please contact us.' }}</p>
<p>{{ $tpl['sign_off'] ?? 'Thank you for your business!' }}</p>
@endsection
