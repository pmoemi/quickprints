@extends('layouts.bms')

@section('content')
<style>
  .cat-pill{
    display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:999px;
    font-size:12px;font-weight:600;background:var(--bg3);border:1px solid var(--border);
    color:var(--text2);text-decoration:none;transition:all .15s;
  }
  .cat-pill:hover{background:var(--accent-dim);border-color:var(--accent);color:var(--accent)}
  .cat-pill.active{background:var(--accent-dim);border-color:var(--accent);color:var(--accent)}
  .cat-pill .pc{
    font-size:10px;font-weight:700;background:var(--bg4);border-radius:999px;
    padding:1px 6px;color:var(--text3);transition:inherit;
  }
  .cat-pill.active .pc,.cat-pill:hover .pc{background:var(--accent-a18);color:var(--accent)}

  .svc-row{display:flex;align-items:center;padding:10px 18px;border-bottom:1px solid var(--border);gap:12px;transition:background .12s;}
  .svc-row:last-child{border-bottom:none}
  .svc-row:hover{background:var(--accent-a06)}
  .svc-num{width:26px;flex-shrink:0;font-size:11px;color:var(--text3);font-family:var(--mono);text-align:right}
  .svc-name{flex:1;min-width:0;font-size:13px;font-weight:500;color:var(--text)}
  .svc-actions{display:flex;gap:4px;flex-shrink:0}

  .cat-hd{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 18px;border-bottom:1px solid var(--border);background:var(--bg3);
  }
  .cat-hd-name{font-size:13px;font-weight:700;color:var(--accent)}
  .cat-hd-link{font-size:12px;color:var(--text3);text-decoration:none;transition:color .15s}
  .cat-hd-link:hover{color:var(--accent)}

  .pg-wrap{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:16px}
  .pg-info{font-size:12px;color:var(--text3)}
  .pg-btns{display:flex;gap:5px;flex-wrap:wrap}
  .pg-btn{
    min-width:32px;height:30px;display:inline-flex;align-items:center;justify-content:center;
    border-radius:6px;font-size:12px;font-weight:600;border:1px solid var(--border);
    background:var(--bg3);color:var(--text2);text-decoration:none;transition:all .15s;padding:0 8px;
  }
  .pg-btn:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-a06)}
  .pg-btn.pg-active{background:var(--accent);border-color:var(--accent);color:#fff}
  .pg-btn.pg-off{opacity:.35;pointer-events:none}
</style>

<div class="page-header">
  <div>
    <div class="page-title">Services Catalogue</div>
    <div class="page-subtitle">{{ number_format($totalAll) }} services &middot; {{ $categories->count() }} categories</div>
  </div>
  <a href="{{ route('bms.services.create') }}" class="btn btn-primary">+ Add Service</a>
</div>

{{-- ── Filter bar (same pattern as Jobs page) ── --}}
<form method="GET" action="{{ route('bms.services.index') }}" class="filter-bar" id="svc-filter-form">
  <input class="search-input" name="q" id="svc-q" value="{{ $search }}"
         placeholder="Search services or categories…" autocomplete="off">
  <select class="filter-select" name="cat" id="svc-cat" onchange="this.form.submit()">
    <option value="">All Categories</option>
    @foreach($categories as $cat)
      <option value="{{ $cat }}" @selected($cat === $category)>{{ $cat }}</option>
    @endforeach
  </select>
  @if($search || $category)
    <a href="{{ route('bms.services.index') }}" class="btn btn-secondary btn-sm">Clear</a>
  @endif
</form>

{{-- ── Category pills ── --}}
<div style="display:flex;flex-wrap:wrap;gap:7px;margin-bottom:18px;">
  <a href="{{ route('bms.services.index', $search ? ['q' => $search] : []) }}"
     class="cat-pill {{ !$category ? 'active' : '' }}">
    All <span class="pc">{{ number_format($totalAll) }}</span>
  </a>
  @foreach($categories as $cat)
    <a href="{{ route('bms.services.index', array_filter(['cat' => $cat, 'q' => $search ?: null])) }}"
       class="cat-pill {{ $cat === $category ? 'active' : '' }}">
      {{ $cat }}
    </a>
  @endforeach
</div>

{{-- ── Results ── --}}
@if($items->isEmpty())
  <div class="card">
    <div class="empty-state">
      <div class="empty-icon">📋</div>
      <p>No services found{{ $search ? ' matching "'.e($search).'"' : '' }}.</p>
      @if($search || $category)
        <a href="{{ route('bms.services.index') }}" class="btn btn-secondary" style="margin-top:14px;">Clear filters</a>
      @endif
    </div>
  </div>
@else

@php $grouped = $items->getCollection()->groupBy('category'); @endphp

@if($category)
  {{-- Single category: one card ── --}}
  <div class="card">
    <div class="cat-hd">
      <span class="cat-hd-name">{{ $category }}</span>
      <div style="display:flex;align-items:center;gap:12px;">
        <span class="badge badge-blue">{{ $items->total() }} services</span>
        <a href="{{ route('bms.services.index', $search ? ['q'=>$search] : []) }}" class="cat-hd-link">← All categories</a>
      </div>
    </div>
    @foreach($items as $i => $item)
      <div class="svc-row">
        <span class="svc-num">{{ ($items->currentPage()-1)*$items->perPage()+$i+1 }}</span>
        <span class="svc-name">{{ $item->name }}</span>
        <div class="svc-actions">
          <a href="{{ route('bms.services.edit', $item->id) }}" class="btn btn-secondary btn-sm">Edit</a>
          <form method="POST" action="{{ route('bms.services.destroy', $item->id) }}"
                onsubmit="return confirm('Delete \'{{ addslashes($item->name) }}\'?')" style="margin:0;">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Del</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@else
  {{-- All categories: grouped cards ── --}}
  @foreach($grouped as $cat => $catItems)
  <div class="card" style="margin-bottom:12px;">
    <div class="cat-hd">
      <span class="cat-hd-name">{{ $cat }}</span>
      <div style="display:flex;align-items:center;gap:12px;">
        <span class="badge badge-blue">{{ $catItems->count() }}</span>
        <a href="{{ route('bms.services.index', array_filter(['cat'=>$cat,'q'=>$search?:null])) }}"
           class="cat-hd-link">View all →</a>
      </div>
    </div>
    @foreach($catItems as $i => $item)
      <div class="svc-row">
        <span class="svc-num">{{ $i+1 }}</span>
        <span class="svc-name">{{ $item->name }}</span>
        <div class="svc-actions">
          <a href="{{ route('bms.services.edit', $item->id) }}" class="btn btn-secondary btn-sm">Edit</a>
          <form method="POST" action="{{ route('bms.services.destroy', $item->id) }}"
                onsubmit="return confirm('Delete \'{{ addslashes($item->name) }}\'?')" style="margin:0;">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm">Del</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
  @endforeach
@endif

{{-- ── Pagination ── --}}
@if($items->hasPages())
<div class="pg-wrap">
  <div class="pg-info">
    Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ number_format($items->total()) }} services
  </div>
  <div class="pg-btns">
    <a href="{{ $items->previousPageUrl() ?? '#' }}"
       class="pg-btn {{ $items->onFirstPage() ? 'pg-off' : '' }}">← Prev</a>
    @foreach($items->getUrlRange(max(1,$items->currentPage()-2), min($items->lastPage(),$items->currentPage()+2)) as $page => $url)
      <a href="{{ $url }}" class="pg-btn {{ $page == $items->currentPage() ? 'pg-active' : '' }}">{{ $page }}</a>
    @endforeach
    <a href="{{ $items->nextPageUrl() ?? '#' }}"
       class="pg-btn {{ !$items->hasMorePages() ? 'pg-off' : '' }}">Next →</a>
  </div>
</div>
@endif

@endif

<script>
(function(){
  var inp = document.getElementById('svc-q');
  var form = document.getElementById('svc-filter-form');
  var timer;
  inp.addEventListener('input', function(){
    clearTimeout(timer);
    timer = setTimeout(function(){ form.submit(); }, 400);
  });
  inp.addEventListener('keydown', function(e){
    if(e.key === 'Enter'){ e.preventDefault(); form.submit(); }
  });
})();
</script>
@endsection
