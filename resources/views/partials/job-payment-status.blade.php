@php
  $status = $job->paymentStatus();
  $map = [
    'pending' => ['label' => 'Pending', 'class' => 'badge-red'],
    'partial' => ['label' => 'Partially paid', 'class' => 'badge-orange'],
    'full'    => ['label' => 'Fully paid', 'class' => 'badge-green'],
  ];
  $meta = $map[$status] ?? $map['pending'];
@endphp
<span class="badge {{ $meta['class'] }}">{{ $meta['label'] }}</span>
