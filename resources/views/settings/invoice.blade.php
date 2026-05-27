@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div class="page-title">Invoice Templates</div>
</div>

@include('settings.tabs')

@php $inv = $settings['invoice'] ?? []; @endphp

@if(session('success'))
  <div style="background:var(--green-dim);color:var(--green);border:1px solid var(--green);border-radius:var(--radius);padding:10px 14px;font-size:13px;margin-bottom:14px;">
    &#10003; {{ session('success') }}
  </div>
@endif
@if($errors->any())
  <div style="background:var(--red-dim);color:var(--red);border:1px solid var(--red);border-radius:var(--radius);padding:10px 14px;font-size:13px;margin-bottom:14px;">
    {{ $errors->first() }}
  </div>
@endif

{{-- Designer launch hero --}}
<div class="card" style="margin-top:8px;">
  <div style="padding:44px 32px;text-align:center;">
    <div style="width:56px;height:56px;border-radius:14px;background:var(--accent);display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:18px;">&#9998;</div>
    <div style="font-size:20px;font-weight:700;color:var(--text1);margin-bottom:10px;">Invoice Template Designer</div>
    <p style="font-size:13px;color:var(--text3);max-width:480px;margin:0 auto 26px;line-height:1.75;">
      Customise the look and feel of your invoices, quotes, and receipts — layout, colors, page margins, footer, watermark, signature and more. Live preview updates instantly as you type.
    </p>
    <a href="{{ route('bms.settings.invoice.design') }}" class="btn btn-primary"
       style="font-size:14px;padding:11px 30px;display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
      Open Template Designer &nbsp;&#8594;
    </a>
  </div>
</div>

{{-- Current settings snapshot --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-top:16px;">
  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">Layout</div>
    <div style="font-weight:700;color:var(--text1);font-size:14px;">{{ ucfirst($inv['layout'] ?? 'classic') }}</div>
  </div>

  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">Accent Color</div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span style="width:20px;height:20px;border-radius:4px;background:{{ $inv['accent_color'] ?? '#f97316' }};border:1px solid rgba(0,0,0,.2);flex-shrink:0;display:inline-block;"></span>
      <span style="font-weight:600;color:var(--text1);font-size:13px;">{{ $inv['accent_color'] ?? '#f97316' }}</span>
    </div>
  </div>

  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">Font Size</div>
    <div style="font-weight:700;color:var(--text1);font-size:14px;">{{ $inv['font_size'] ?? 13 }} px</div>
  </div>

  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">Page Margins (cm)</div>
    <div style="font-size:12px;color:var(--text1);line-height:1.9;">
      T&nbsp;{{ $inv['margin_top'] ?? '1.5' }} &middot;
      R&nbsp;{{ $inv['margin_right'] ?? '1.5' }} &middot;
      B&nbsp;{{ $inv['margin_bottom'] ?? '1.5' }} &middot;
      L&nbsp;{{ $inv['margin_left'] ?? '1.5' }}
    </div>
  </div>

  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">Footer</div>
    <div style="font-size:12px;color:var(--text1);">
      @if($inv['show_footer'] ?? true)
        <span style="color:var(--green);font-weight:600;">Visible</span>
        <div style="color:var(--text3);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">{{ $inv['footer_text'] ?? '' }}</div>
      @else
        <span style="color:var(--text3);">Hidden</span>
      @endif
    </div>
  </div>

  <div class="card" style="padding:16px;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:8px;">VAT Column</div>
    <div style="font-weight:600;font-size:13px;color:{{ ($inv['show_vat_column'] ?? true) ? 'var(--green)' : 'var(--text3)' }};">
      {{ ($inv['show_vat_column'] ?? true) ? 'Shown' : 'Hidden' }}
    </div>
  </div>
</div>

@endsection
