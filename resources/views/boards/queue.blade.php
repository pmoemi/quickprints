@extends('layouts.bms')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Production Queue</div>
    <div class="page-subtitle">Full production pipeline view</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">
  <div class="card" style="text-align:center;">
    <div style="font-size:28px;margin-bottom:8px;">🎨</div>
    <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Designer Board</div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Jobs awaiting design work</p>
    <a href="{{ route('bms.designer') }}" class="btn btn-secondary btn-sm">View</a>
  </div>
  <div class="card" style="text-align:center;">
    <div style="font-size:28px;margin-bottom:8px;">⚙️</div>
    <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Operator Queue</div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Jobs in printing & production</p>
    <a href="{{ route('bms.operator') }}" class="btn btn-secondary btn-sm">View</a>
  </div>
  <div class="card" style="text-align:center;">
    <div style="font-size:28px;margin-bottom:8px;">🔩</div>
    <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Fabrication Queue</div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Jobs in fabrication stage</p>
    <a href="{{ route('bms.fabrication') }}" class="btn btn-secondary btn-sm">View</a>
  </div>
  <div class="card" style="text-align:center;">
    <div style="font-size:28px;margin-bottom:8px;">🚚</div>
    <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Delivery & Install</div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:14px;">Ready for delivery / installation</p>
    <a href="{{ route('bms.delivery') }}" class="btn btn-secondary btn-sm">View</a>
  </div>
</div>
@endsection
