@extends('emails._layout')

@section('content')
@php
  $stageMessages = [
    'designing'   => 'Our design team is working on your job.',
    'approval'    => 'Your design is ready for approval. Please review and confirm.',
    'printing'    => 'Your job is now in the printing stage.',
    'fabrication' => 'Your job is in fabrication.',
    'ready'       => 'Great news — your job is ready for collection/delivery!',
    'installed'   => 'Your job has been installed successfully.',
    'paid'        => 'Payment confirmed. Thank you!',
  ];
  $stageMsg = $stageMessages[$job->stage] ?? 'Your job status has been updated.';
@endphp

<h2>Job Update: {{ ucfirst($job->stage) }}</h2>
<p>{{ $tpl['greeting'] ?? 'Hi ' . ($client?->name ?? 'Valued Customer') . ',' }}</p>
<p>{{ $stageMsg }}</p>

<table class="info">
  <tr>
    <td class="lbl">Job ID</td>
    <td class="val" style="font-family:monospace;">{{ $job->id }}</td>
  </tr>
  <tr>
    <td class="lbl">Description</td>
    <td class="val">{{ $job->title }}</td>
  </tr>
  <tr>
    <td class="lbl">Previous Status</td>
    <td class="val"><span class="badge badge-stage">{{ ucfirst($oldStage) }}</span></td>
  </tr>
  <tr>
    <td class="lbl">Current Status</td>
    <td class="val"><span class="badge" style="background:#d1fae5;color:#065f46;">{{ ucfirst($job->stage) }}</span></td>
  </tr>
  @if($job->deadline)
  <tr>
    <td class="lbl">Deadline</td>
    <td class="val">{{ $job->deadline->format('d M Y') }}</td>
  </tr>
  @endif
  <tr>
    <td class="lbl">Branch</td>
    <td class="val">{{ $job->branch }}</td>
  </tr>
</table>

@if(in_array($job->stage, ['ready', 'installed']))
<div class="note">
  <strong>Next step:</strong> Please contact us to arrange collection or delivery.
  @if(!empty($settings['phone'])) Call us at {{ $settings['phone'] }}.@endif
</div>
@endif

<p>{{ $tpl['closing'] ?? 'Thank you for choosing ' . ($settings['company_name'] ?? 'Quick Prints') . '.' }}</p>
@endsection
