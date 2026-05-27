@php
  use App\Support\DocTemplateEngine;
  use App\Support\BrandAssets;
  $engine  = new DocTemplateEngine($settings);
  $inv     = $settings['invoice'] ?? [];
  $symbol  = $settings['currency_symbol'] ?? 'KSh';
  $vatRate = (float)($settings['vat_rate'] ?? 16);
  $vatAmt  = $job->amount * $vatRate / (100 + $vatRate);
  $net     = $job->amount - $vatAmt;
  $showVat = $engine->get('show_vat_column', true);
  $accent  = $engine->accent();
  $isFormal = $engine->layout() === 'formal';

  $docRef  = e($job->id)
    . '<br>' . now()->format('d M Y')
    . ($job->deadline ? '<br>Due: ' . $job->deadline->format('d M Y') : '');
  $docMeta = $job->paid
    ? '<span class="badge badge-paid">PAID</span>'
    : '<span class="badge badge-unpaid">UNPAID</span>';

  $artworkUrl = $job->artwork_url ?? '';
  $hasLogo = BrandAssets::hasLogo($settings);
  $toolbarLogo = BrandAssets::logoForTheme($settings, 'light');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="{{ $brand['primary'] }}">
<title>Invoice {{ $job->id }} · {{ $settings['company_name'] ?? 'QuickPrints' }}</title>
@if(!empty($settings['favicon_url']))
  <link rel="icon" href="{{ $settings['favicon_url'] }}">
@endif
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'IBM Plex Sans',Arial,sans-serif;background:#f1f5f9;color:#111;
  padding:env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
  -webkit-text-size-adjust:100%;
}
.toolbar{
  background:#fff;border-bottom:1px solid #e2e8f0;padding:10px 20px;display:flex;flex-wrap:wrap;
  gap:8px;align-items:center;position:sticky;top:0;z-index:10;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
  padding-top:max(10px, env(safe-area-inset-top));
}
.toolbar-brand{display:flex;align-items:center;gap:10px;margin-right:auto;min-width:0;flex:1 1 auto}
.toolbar-brand img{height:32px;max-width:120px;object-fit:contain}
.toolbar-brand span{font-size:14px;font-weight:700;color:{{ $brand['primary'] }}}
.toolbar-title{
  font-size:12px;font-weight:600;color:#64748b;font-family:'IBM Plex Mono',monospace;
  width:100%;order:-1;margin-bottom:2px;
}
.toolbar-actions{display:flex;flex-wrap:wrap;gap:8px;width:100%}
.tbtn{
  padding:10px 14px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;
  text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:5px;
  transition:opacity .15s;min-height:44px;touch-action:manipulation;-webkit-tap-highlight-color:transparent;
}
.tbtn:hover{opacity:.9}
.tbtn-primary{background:{{ $brand['primary'] }};color:#fff;}
.tbtn-secondary{background:{{ $brand['secondary'] }};color:#fff;}
.tbtn-neutral{background:#f1f5f9;color:#374151;border:1px solid #d1d5db;}
.doc-outer{
  max-width:800px;margin:28px auto;background:#fff;border-radius:10px;
  box-shadow:0 4px 28px rgba(0,0,0,.09);overflow:hidden;
}
.doc-inner{padding:{{ $engine->marginShorthand() }};}
{!! $engine->css(true) !!}
@media(min-width:641px){
  .toolbar-title{width:auto;order:0;margin-bottom:0}
  .toolbar-actions{width:auto;flex:0 0 auto;margin-left:auto}
}
@media(max-width:640px){
  .toolbar{padding:12px 14px;gap:10px}
  .toolbar-brand img{max-width:100px;height:28px}
  .toolbar-actions .tbtn{flex:1 1 calc(50% - 4px);min-width:0;font-size:12px;padding:10px 12px}
  .toolbar-actions .tbtn-neutral:last-child{flex:1 1 100%}
  .doc-outer{margin:12px;border-radius:8px}
  .doc-inner{padding:16px!important}
}
@media(max-width:400px){
  .toolbar-actions .tbtn{flex:1 1 100%}
}
@media print{
  .toolbar{display:none!important}
  body{background:#fff}
  .doc-outer{margin:0;box-shadow:none;border-radius:0}
}
</style>
</head>
<body>

<div class="toolbar">
  <div class="toolbar-brand">
    @if($hasLogo)
      <img src="{{ $toolbarLogo }}" alt="{{ $settings['company_name'] ?? 'Logo' }}">
    @else
      <span>{{ $settings['company_name'] ?? 'QuickPrints' }}</span>
    @endif
  </div>
  <span class="toolbar-title">Invoice {{ $job->id }}</span>

  <div class="toolbar-actions">
    <a href="{{ route('portal.public.invoice.pdf', [$portal->token, $job->id]) }}" class="tbtn tbtn-primary" target="_blank">↓ Download PDF</a>
    @if($job->paid)
      <a href="{{ route('portal.public.receipt.pdf', [$portal->token, $job->id]) }}" class="tbtn tbtn-secondary" target="_blank">↓ Receipt</a>
    @endif
    <button type="button" onclick="window.print()" class="tbtn tbtn-neutral">Print</button>
    <a href="{{ route('portal.public', $portal->token) }}" class="tbtn tbtn-neutral">← Back to portal</a>
  </div>
</div>

@include('partials.invoice-document')

</body>
</html>
