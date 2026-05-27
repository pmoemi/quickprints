<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="{{ $bmsBrand['primary'] ?? '#b91c1c' }}">
<title>{{ $bmsSettings['company_name'] ?? config('app.name', 'QuickPrints') }} — BMS</title>
@include('partials.favicon', ['settings' => $bmsSettings])
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #111318;
  --bg2: #1a1d24;
  --bg3: #222730;
  --bg4: #2a2f3a;
  --border: #2e3340;
  --border2: #3a4050;
  --text: #e8eaf0;
  --text2: #9ba3b8;
  --text3: #5a6280;
  --accent: {{ $bmsBrand['primary'] ?? '#b91c1c' }};
  --accent2: {{ $bmsBrand['secondary'] ?? '#dc2626' }};
  --accent-rgb: {{ $bmsBrand['rgb'] ?? '185, 28, 28' }};
  --accent-dim: rgba(var(--accent-rgb), 0.16);
  --accent-a07: rgba(var(--accent-rgb), 0.07);
  --accent-a08: rgba(var(--accent-rgb), 0.08);
  --accent-a12: rgba(var(--accent-rgb), 0.12);
  --accent-a14: rgba(var(--accent-rgb), 0.14);
  --accent-a18: rgba(var(--accent-rgb), 0.18);
  --accent-a25: rgba(var(--accent-rgb), 0.25);
  --brand-dark: color-mix(in srgb, var(--accent) 42%, #000);
  --brand-deep: color-mix(in srgb, var(--accent) 62%, #000);
  --green: #16a34a;
  --green-dim: rgba(22,163,74,0.14);
  --red: #dc2626;
  --red-dim: rgba(220,38,38,0.14);
  --blue: #1d4ed8;
  --blue-dim: rgba(29,78,216,0.14);
  --yellow: #d97706;
  --yellow-dim: rgba(217,119,6,0.16);
  --purple: #7c3aed;
  --purple-dim: rgba(124,58,237,0.14);
  --teal: #0d9488;
  --teal-dim: rgba(13,148,136,0.14);
  --orange: #ea580c;
  --orange-dim: rgba(234,88,12,0.14);
  --sidebar-w: 220px;
  --topbar-h: 48px;
  --radius: 6px;
  --font: 'IBM Plex Sans', 'Helvetica Neue', Arial, sans-serif;
  --mono: 'IBM Plex Mono', 'Courier New', monospace;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--font);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
body.theme-light{
  --bg:#f0f2f5;--bg2:#ffffff;--bg3:#f5f6f8;--bg4:#eceef1;
  --border:#e2e5ea;--border2:#d0d5dd;--text:#1a1d23;--text2:#5a6070;--text3:#9099a8;
  --accent-dim:rgba(var(--accent-rgb), 0.12);
  --green:#16a34a;--green-dim:rgba(22,163,74,0.12);--red:#dc2626;--red-dim:rgba(220,38,38,0.12);
  --blue:#1d4ed8;--blue-dim:rgba(29,78,216,0.12);--yellow:#d97706;--yellow-dim:rgba(217,119,6,0.14);
  --purple:#7c3aed;--purple-dim:rgba(124,58,237,0.12);--teal:#0d9488;--teal-dim:rgba(13,148,136,0.12);
  --orange:#ea580c;--orange-dim:rgba(234,88,12,0.12);
}
a{color:inherit;text-decoration:none;}
button{font-family:var(--font);cursor:pointer;border:none;outline:none;}
input,select,textarea{font-family:var(--font);outline:none;}

/* LAYOUT */
#sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sidebar-w);background:#1e2227;border-right:none;display:flex;flex-direction:column;z-index:100;overflow-y:auto;box-shadow:2px 0 8px rgba(0,0,0,.12);}
#nav-menu{flex:1;min-height:0;overflow-y:auto;overflow-x:hidden;padding-bottom:10px;}
.sidebar-logo{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0;background:#1e2227;}
.sidebar-logo img{height:28px;max-width:150px;object-fit:contain;display:block}
.sidebar-logo h2{font-size:14px;font-weight:700;color:var(--accent);letter-spacing:.5px;line-height:1.2}
.sidebar-logo p{font-size:10px;color:rgba(255,255,255,.4);margin-top:4px;font-family:var(--mono);}
.sidebar-user{padding:12px 18px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0;background:#1a1e23;}
.sidebar-user .u-name{font-size:13px;font-weight:600;color:var(--text);}
.sidebar-user .u-role{font-size:11px;color:var(--text3);margin-top:2px;}
.nav-section{padding:8px 0;}
.nav-label{font-size:10px;font-weight:700;color:rgba(255,255,255,.34);text-transform:uppercase;letter-spacing:.08em;padding:7px 14px 5px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;font-size:13px;color:rgba(255,255,255,.55);cursor:pointer;transition:all .15s;border-left:3px solid transparent;border-radius:var(--radius);margin:0 8px 1px;}
.nav-item:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.9);}
.nav-item.active{background:var(--accent-a18);color:#ffffff;border-left-color:var(--accent);font-weight:600;padding-left:9px;}
.nav-item .nav-icon{font-size:14px;width:16px;text-align:center;}
.sidebar-bottom{margin-top:auto;padding:12px 18px;border-top:1px solid var(--border);flex-shrink:0;}
.sync-status{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text3);}
.sync-dot{width:6px;height:6px;border-radius:50%;background:var(--green);}

/* LIGHT MODE — sidebar & mobile nav overrides */
body.theme-light #sidebar{background:#ffffff;border-right:1px solid #e2e5ea;box-shadow:2px 0 8px rgba(0,0,0,.06);}
body.theme-light .sidebar-logo{background:#ffffff;border-bottom-color:#e2e5ea;}
body.theme-light .sidebar-logo p{color:#9099a8;}
body.theme-light .sidebar-user{background:#f8f9fb;border-bottom-color:#e2e5ea;}
body.theme-light .sidebar-user .u-name{color:#1a1d23;}
body.theme-light .sidebar-user .u-role{color:#6b7280;}
body.theme-light .nav-label{color:#9099a8;}
body.theme-light .nav-item{color:#5a6070;}
body.theme-light .nav-item:hover{background:#f0f2f5;color:#1a1d23;}
body.theme-light .nav-item.active{background:var(--accent-a08);color:var(--accent);border-left-color:var(--accent);}
body.theme-light .sidebar-bottom{border-top-color:#e2e5ea;}
body.theme-light .sync-status{color:#9099a8;}
body.theme-light .mobile-nav{background:#ffffff;border-top-color:#e2e5ea;box-shadow:0 -4px 14px rgba(0,0,0,.07);}
body.theme-light .mobile-nav a{color:#9099a8;}
body.theme-light .mobile-nav a.active{color:var(--accent);background:var(--accent-a07);}

#topbar{position:fixed;left:var(--sidebar-w);right:0;top:0;height:var(--topbar-h);background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 14px;gap:8px;z-index:99;box-shadow:0 1px 4px rgba(0,0,0,.12);}
.topbar-title{font-size:13px;font-weight:700;color:var(--text);flex:1;}
.branch-select{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:5px 9px;color:var(--text);font-size:12px;cursor:pointer;height:30px;}
.btn-topbar{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:5px 9px;color:var(--text2);font-size:12px;transition:all .15s;height:30px;display:inline-flex;align-items:center;}
.btn-topbar:hover{color:var(--text);border-color:var(--border2);}
.notif-btn{position:relative;background:none;border:none;color:var(--text2);font-size:16px;cursor:pointer;padding:4px;}
.notif-badge{position:absolute;top:-3px;right:-3px;background:var(--accent);color:#fff;font-size:9px;font-weight:700;border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center;}
.btn-logout{background:none;border:1px solid var(--border);border-radius:var(--radius);padding:5px 10px;color:var(--text3);font-size:12px;transition:all .15s;height:30px;display:inline-flex;align-items:center;}
.btn-logout:hover{border-color:var(--red);color:var(--red);}

#main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);padding:24px;min-height:calc(100vh - var(--topbar-h));}

/* CARDS */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.08);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.card-title{font-size:13px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:.04em;}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;}
.page-title{font-size:20px;font-weight:700;color:var(--text);}
.page-subtitle{font-size:12px;color:var(--text3);margin-top:2px;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
.grid-auto{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;}

/* STAT CARDS */
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.08);position:relative;overflow:hidden;transition:border-color .2s,box-shadow .2s,transform .2s;}
.stat-card:hover{border-color:var(--border2);box-shadow:0 4px 16px rgba(0,0,0,.12);transform:translateY(-1px);}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--stat-accent,var(--accent));opacity:.85;}
.stat-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;}
.stat-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;background:var(--stat-icon-bg,var(--accent-dim));}
.stat-label{font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:0;}
.stat-value{font-size:26px;font-weight:700;color:var(--text);font-family:var(--mono);}
.stat-value.accent{color:var(--accent);}
.stat-value.green{color:var(--green);}
.stat-value.red{color:var(--red);}
.stat-value.blue{color:var(--blue);}
.stat-sub{font-size:11px;color:var(--text3);margin-top:4px;}
.stat-trend{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;margin-top:6px;}
.stat-trend.up{color:var(--green);background:var(--green-dim);}
.stat-trend.down{color:var(--red);background:var(--red-dim);}
.stat-trend.neutral{color:var(--text3);background:var(--bg4);}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;border:none;}
.btn-primary{background:var(--accent);color:#fff;}.btn-primary:hover{background:var(--accent2);}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border);}.btn-secondary:hover{border-color:var(--border2);}
.btn-danger{background:var(--red-dim);color:var(--red);border:1px solid transparent;}.btn-danger:hover{background:var(--red);color:#fff;}
.btn-success{background:var(--green-dim);color:var(--green);border:1px solid transparent;}.btn-success:hover{background:var(--green);color:#fff;}
.btn-sm{padding:4px 10px;font-size:12px;}
.btn-icon{padding:6px;width:30px;height:30px;justify-content:center;}

/* TABLE */
.tbl-wrap{overflow-x:auto;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);}
table{width:100%;border-collapse:collapse;}
th{font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;padding:8px 12px;border-bottom:1px solid var(--border);text-align:left;background:var(--bg2);}
td{font-size:13px;color:var(--text);padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:hover td{background:var(--bg3);}
tr:last-child td{border-bottom:none;}

/* BADGES */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:600;white-space:nowrap;border:1px solid transparent;letter-spacing:.02em;}
.badge-orange{background:var(--orange-dim);color:var(--orange);border-color:rgba(234,88,12,.25);}
.badge-green{background:var(--green-dim);color:var(--green);border-color:rgba(22,163,74,.25);}
.badge-red{background:var(--red-dim);color:var(--red);border-color:rgba(220,38,38,.25);}
.badge-blue{background:var(--blue-dim);color:var(--blue);border-color:rgba(29,78,216,.25);}
.badge-yellow{background:var(--yellow-dim);color:var(--yellow);border-color:rgba(217,119,6,.25);}
.badge-purple{background:var(--purple-dim);color:var(--purple);border-color:rgba(124,58,237,.25);}
.badge-gray{background:var(--bg4);color:var(--text3);border-color:var(--border);}
.stage-waiting{background:var(--blue-dim);color:var(--blue);border:1px solid rgba(29,78,216,.2);}
.stage-designing{background:var(--purple-dim);color:var(--purple);border:1px solid rgba(124,58,237,.2);}
.stage-approval{background:rgba(14,165,233,.14);color:#0369a1;border:1px solid rgba(14,165,233,.25);}
.stage-printing{background:var(--teal-dim);color:var(--teal);border:1px solid rgba(13,148,136,.25);}
.stage-fabrication{background:var(--orange-dim);color:var(--orange);border:1px solid rgba(234,88,12,.25);}
.stage-ready{background:var(--green-dim);color:var(--green);border:1px solid rgba(22,163,74,.25);}
.stage-installed{background:rgba(6,182,212,.14);color:#0e7490;border:1px solid rgba(6,182,212,.25);}
.stage-paid{background:var(--green-dim);color:var(--green);border:1px solid rgba(22,163,74,.25);}

/* FORM ELEMENTS */
.form-row{display:grid;gap:14px;margin-bottom:14px;}
.form-row.cols-2{grid-template-columns:1fr 1fr;}
.form-row.cols-3{grid-template-columns:1fr 1fr 1fr;}
.fld label{display:block;font-size:11px;font-weight:600;color:var(--text2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px;}
.fld input,.fld select,.fld textarea{width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:8px 10px;color:var(--text);font-size:13px;transition:border-color .2s;}
.fld input:focus,.fld select:focus,.fld textarea:focus{border-color:var(--accent);}
.fld select{cursor:pointer;}
.fld textarea{resize:vertical;min-height:80px;}
.fld select option{background:var(--bg2);}

/* FILTER BAR */
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;}
.search-input{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:7px 12px;color:var(--text);font-size:13px;min-width:200px;transition:border-color .2s;}
.search-input:focus{border-color:var(--accent);}
.filter-select{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:7px 10px;color:var(--text);font-size:13px;cursor:pointer;}

/* KANBAN */
.kanban-wrap{display:flex;gap:14px;overflow-x:auto;padding-bottom:10px;}
.kanban-col{flex-shrink:0;width:230px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);display:flex;flex-direction:column;max-height:calc(100vh - 160px);}
.kanban-col-header{padding:10px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.kanban-col-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.kanban-col-count{font-size:11px;color:var(--text3);background:var(--bg4);padding:1px 7px;border-radius:20px;}
.kanban-cards{padding:10px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;flex:1;}
.k-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:10px 12px;cursor:pointer;transition:border-color .15s;}
.k-card:hover{border-color:var(--border2);}
.k-card-id{font-size:10px;color:var(--text3);font-family:var(--mono);margin-bottom:3px;}
.k-card-title{font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;line-height:1.4;}
.k-card-client{font-size:11px;color:var(--text2);}
.k-card-footer{display:flex;align-items:center;justify-content:space-between;margin-top:8px;}
.k-card-amount{font-size:11px;font-family:var(--mono);color:var(--accent);}
.priority-dot{width:7px;height:7px;border-radius:50%;}
.priority-high{background:var(--red);}
.priority-medium{background:var(--yellow);}
.priority-low{background:var(--green);}

/* TIMELINE */
.timeline{display:flex;flex-direction:column;gap:0;}
.tl-item{display:flex;gap:12px;padding:10px 0;position:relative;}
.tl-item:not(:last-child)::after{content:'';position:absolute;left:14px;top:30px;bottom:0;width:1px;background:var(--border);}
.tl-dot{width:8px;height:8px;border-radius:50%;background:var(--accent);flex-shrink:0;margin-top:6px;}
.tl-content{flex:1;}
.tl-action{font-size:13px;color:var(--text);}
.tl-meta{font-size:11px;color:var(--text3);margin-top:2px;}

/* WIDGETS */
.activity-item{display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid var(--border);}
.activity-item:last-child{border-bottom:none;}
.activity-icon{width:32px;height:32px;border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.activity-text{flex:1;font-size:13px;color:var(--text);}
.activity-time{font-size:11px;color:var(--text3);}

/* TABS */
.tabs{display:flex;gap:2px;background:var(--bg3);border-radius:var(--radius);padding:3px;margin-bottom:20px;border:1px solid var(--border);overflow-x:auto;-webkit-overflow-scrolling:touch;}
.tab-item{padding:6px 14px;border-radius:4px;font-size:13px;font-weight:500;color:var(--text2);cursor:pointer;transition:all .15s;text-decoration:none;white-space:nowrap;flex-shrink:0;}
.tab-item.active{background:var(--bg2);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,.3);}

/* MESSAGES */
.msg-layout{display:grid;grid-template-columns:240px 1fr;height:calc(100vh - var(--topbar-h) - 48px);gap:0;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
.msg-list{border-right:1px solid var(--border);overflow-y:auto;}
.msg-list-item{padding:12px 14px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .15s;display:block;}
.msg-list-item:hover,.msg-list-item.active{background:var(--bg3);}
.msg-from{font-size:13px;font-weight:600;color:var(--text);}
.msg-subject{font-size:12px;color:var(--text2);margin-top:2px;}
.msg-preview{font-size:11px;color:var(--text3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.msg-pane{display:flex;flex-direction:column;}
.msg-pane-header{padding:16px 20px;border-bottom:1px solid var(--border);}
.msg-pane-body{padding:20px;flex:1;overflow-y:auto;font-size:14px;color:var(--text2);line-height:1.7;}
.msg-pane-empty{flex:1;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:14px;}

/* EMPTY STATE */
.empty-state{text-align:center;padding:48px 20px;color:var(--text3);}
.empty-state .empty-icon{font-size:36px;margin-bottom:12px;}
.empty-state p{font-size:14px;}

/* SCROLLBAR */
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px;}

/* PRIORITY */
.p-high{color:var(--red);font-size:11px;font-weight:700;}
.p-medium{color:var(--yellow);font-size:11px;font-weight:700;}
.p-low{color:var(--green);font-size:11px;font-weight:700;}

/* AVATAR */
.avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;}

/* ALERT */
.alert{padding:10px 14px;border-radius:var(--radius);font-size:13px;margin-bottom:14px;}
.alert-info{background:var(--blue-dim);color:var(--blue);border:1px solid rgba(59,130,246,.2);}
.alert-warn{background:var(--yellow-dim);color:var(--yellow);border:1px solid rgba(234,179,8,.2);}
.alert-danger{background:var(--red-dim);color:var(--red);border:1px solid rgba(239,68,68,.2);}
.alert-success{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,.2);}

/* TOAST */
#toast{position:fixed;bottom:20px;right:20px;z-index:9000;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.toast-item{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:10px 16px;font-size:13px;color:var(--text);box-shadow:0 4px 20px rgba(0,0,0,.5);transform:translateX(0);transition:all .3s;pointer-events:all;display:flex;align-items:center;gap:8px;}
.toast-item.toast-success{border-left:3px solid var(--green);}
.toast-item.toast-error{border-left:3px solid var(--red);}
.toast-item.toast-info{border-left:3px solid var(--blue);}
.toast-item.leaving{opacity:0;transform:translateX(20px);}

/* BAR CHART */
.bar-chart{display:flex;flex-direction:column;gap:8px;}
.bar-row{display:flex;align-items:center;gap:10px;}
.bar-label{font-size:12px;color:var(--text2);width:100px;text-align:right;flex-shrink:0;}
.bar-track{flex:1;background:var(--bg4);border-radius:3px;height:22px;overflow:hidden;}
.bar-fill{height:100%;background:var(--accent);border-radius:3px;display:flex;align-items:center;padding:0 8px;font-size:11px;color:#fff;font-weight:600;}
.bar-val{font-size:12px;color:var(--text2);width:70px;font-family:var(--mono);}

/* SIDEBAR CLOSE BUTTON (mobile only) */
#sidebar-close{display:none;}

/* SIDEBAR BACKDROP */
#sidebar-backdrop{
  display:none;position:fixed;inset:0;z-index:99;
  background:rgba(0,0,0,.55);backdrop-filter:blur(2px);
}
#sidebar-backdrop.open{display:block;}

/* MOBILE NAV */
.mobile-nav{display:none;}

/* RESPONSIVE */
@media(max-width:768px){
  :root{--sidebar-w:0px;}
  #sidebar{transform:translateX(-220px);transition:transform .3s;}
  #sidebar.open{transform:translateX(0);width:220px;}
  #sidebar-close{
    display:flex;align-items:center;justify-content:center;
    position:absolute;top:10px;right:10px;
    width:28px;height:28px;border-radius:50%;
    background:var(--accent);border:none;
    color:#fff;font-size:13px;font-weight:700;cursor:pointer;
    line-height:1;z-index:2;box-shadow:0 2px 6px rgba(0,0,0,.25);
  }
  #sidebar-close:hover{opacity:.85;}
  .sidebar-logo{position:relative;padding-right:44px;}
  #main{padding:14px 12px 96px;}
  #topbar{padding:0 8px;gap:6px;left:0;}
  .grid-4{grid-template-columns:1fr 1fr;}
  .grid-3{grid-template-columns:1fr 1fr;}
  .grid-2{grid-template-columns:1fr;}
  /* Form rows collapse to single column */
  .form-row.cols-2,.form-row.cols-3{grid-template-columns:1fr;}
  /* Filter bar fills width */
  .filter-bar{flex-direction:column;align-items:stretch;}
  .search-input{min-width:0;width:100%;}
  .filter-select{width:100%;}
  /* Tables: allow horizontal scroll, tighten cells */
  td,th{padding:8px 10px;font-size:12px;}
  .page-title{font-size:17px;}
  .page-header{margin-bottom:12px;}
  /* Card padding tighter */
  .card{padding:12px;}
  .mobile-nav{
    position:fixed;left:0;right:0;bottom:0;z-index:120;
    display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0;
    background:#1e2227;border-top:1px solid rgba(255,255,255,.08);
    padding:4px 0;box-shadow:0 -6px 18px rgba(0,0,0,.28);
  }
  .mobile-nav a{
    background:transparent;color:rgba(255,255,255,.45);
    font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
    padding:6px 2px;display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:2px;min-height:54px;border-radius:var(--radius);text-decoration:none;
  }
  .mobile-nav a.active{color:var(--accent);background:var(--accent-a14);}
  .mobile-nav .ico{font-size:18px;line-height:1;}
  .kanban-wrap{padding-bottom:80px;}
}
@media(hover:none){
  .btn{min-height:36px;}
  .nav-item{min-height:42px;}
  input,select,textarea{min-height:40px;font-size:16px;}
}

/* SETTINGS TABS */
.settings-tabs{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:24px;overflow-x:auto;-webkit-overflow-scrolling:touch;}
.settings-tab{padding:10px 18px;font-size:13px;font-weight:500;color:var(--text2);border-bottom:2px solid transparent;cursor:pointer;white-space:nowrap;text-decoration:none;transition:all .15s;flex-shrink:0;}
.settings-tab:hover{color:var(--text);}
.settings-tab.active{color:var(--accent);border-bottom-color:var(--accent);}
@media(max-width:768px){
  .settings-tab{padding:8px 12px;font-size:12px;}
  .tab-item{padding:5px 10px;font-size:12px;}
}

/* PAGE TRANSITION BAR */
#page-loader{position:fixed;top:0;left:0;right:0;height:3px;z-index:99999;pointer-events:none;background:transparent;}
#page-loader-bar{height:100%;width:0%;background:var(--accent);border-radius:0 2px 2px 0;transition:width .25s ease;box-shadow:0 0 8px var(--accent-a25);}

/* MISC */
.mono{font-family:var(--mono);}
.text-accent{color:var(--accent);}
.text-green{color:var(--green);}
.text-red{color:var(--red);}
.text-blue{color:var(--blue);}
.text-yellow{color:var(--yellow);}
.text-muted{color:var(--text3);}
.mb-0{margin-bottom:0;}
.mb-1{margin-bottom:8px;}
.mb-2{margin-bottom:16px;}
.mb-3{margin-bottom:24px;}
</style>
@stack('styles')
</head>
<body class="{{ ($bmsSettings['theme'] ?? 'dark') === 'light' ? 'theme-light' : '' }}">
@php
  use App\Support\BrandAssets;
  $bmsTheme = ($bmsSettings['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';
  $bmsLogos = BrandAssets::logos($bmsSettings);
  $bmsHasLogo = BrandAssets::hasLogo($bmsSettings);
@endphp

<div id="sidebar-backdrop"></div>
<div id="sidebar">
  <div class="sidebar-logo">
    <button id="sidebar-close" onclick="closeSidebar()" aria-label="Close menu">&#10005;</button>
    @if($bmsHasLogo)
      <img
        id="bms-brand-logo"
        src="{{ BrandAssets::logoForTheme($bmsSettings, $bmsTheme) }}"
        alt="{{ $bmsSettings['company_name'] ?? 'Logo' }}"
        data-logo-dark="{{ $bmsLogos['dark'] ?? '' }}"
        data-logo-light="{{ $bmsLogos['light'] ?? '' }}"
      >
    @else
      <h2>{{ strtoupper($bmsSettings['company_name'] ?? 'QUICK PRINTS') }}</h2>
    @endif
    <p>{{ $bmsBranch === 'all' ? 'All Branches' : $bmsBranch }}</p>
  </div>
  <div class="sidebar-user">
    <a href="{{ route('bms.profile') }}" style="text-decoration:none;color:inherit;display:block;">
      <div class="u-name">{{ $bmsUser?->name ?? '—' }}</div>
      <div class="u-role" style="display:flex;align-items:center;gap:6px;">
        <span>{{ $bmsUser?->role ?? '—' }}</span>
        <span style="font-size:10px;opacity:.55;letter-spacing:.03em;">· Edit profile</span>
      </div>
    </a>
  </div>
  <div id="nav-menu">
    @foreach($bmsNav as $section)
      <div class="nav-section">
        <div class="nav-label">{{ $section['section'] }}</div>
        @foreach($section['items'] as $item)
          <a href="{{ route($item['route']) }}" class="nav-item {{ request()->routeIs($item['route']) || (isset($item['route_prefix']) && request()->routeIs($item['route_prefix'].'*')) ? 'active' : '' }}">
            <span class="nav-icon">{{ $item['icon'] }}</span>{{ $item['label'] }}
          </a>
        @endforeach
      </div>
    @endforeach
  </div>
  <div class="sidebar-bottom">
    <div class="sync-status">
      <div class="sync-dot"></div>
      <span>BMS Online</span>
    </div>
  </div>
</div>

<div id="topbar">
  @php
    $routeTitles = [
      'bms.dashboard' => 'Dashboard',
      'bms.kanban' => 'Kanban Board',
      'bms.jobs.index' => 'Job Tracker',
      'bms.saleslog.index' => 'Daily Sales Log',
      'bms.quotes.index' => 'Quote Builder',
      'bms.leads.index' => 'Leads',
      'bms.followups' => 'Follow-ups',
      'bms.clients.index' => 'Clients',
      'bms.inventory.index' => 'Materials / Inventory',
      'bms.suppliers.index' => 'Suppliers',
      'bms.purchase-orders.index' => 'Purchase Orders',
      'bms.opex.index' => 'Expenses (Opex)',
      'bms.payroll.index' => 'Payroll',
      'bms.pettycash.index' => 'Petty Cash',
      'bms.assets.index' => 'Assets',
      'bms.procurement.index' => 'Procurement',
      'bms.staff.index' => 'Staff',
      'bms.attendance.index' => 'Attendance',
      'bms.leave.index' => 'Leave',
      'bms.payslips.index' => 'Payslips',
      'bms.messages.index' => 'Messages',
      'bms.notifications.index' => 'Notifications',
      'bms.reports' => 'Reports',
      'bms.commissions.index' => 'Commissions',
      'bms.sales-targets.index' => 'Sales Targets',
      'bms.ledger.index' => 'Accounting Ledger',
      'bms.bank-recon' => 'Bank Reconciliation',
      'bms.cash-recon' => 'Cash Reconciliation',
      'bms.vat-report' => 'VAT Report',
      'bms.portal.index' => 'Client Portal',
      'bms.audit.index' => 'Audit Log',
      'bms.maintenance.index' => 'Maintenance',
      'bms.recurring-bills.index' => 'Recurring Bills',
      'bms.settings.company' => 'Settings',
      'bms.settings.branches' => 'Settings',
      'bms.settings.branding' => 'Settings',
      'bms.settings.invoice' => 'Settings',
      'bms.settings.finance' => 'Settings',
      'bms.settings.roles' => 'Settings',
      'bms.services.index' => 'Services Catalogue',
      'bms.payments' => 'Payments',
      'bms.designer' => 'Designer Board',
      'bms.operator' => 'Operator Queue',
      'bms.fabrication' => 'Fabrication Queue',
      'bms.delivery' => 'Delivery & Installation',
    ];
    $pageTitle = $routeTitles[collect($routeTitles)->keys()->first(fn($k) => request()->routeIs($k))] ?? ($title ?? 'BMS');
  @endphp
  <div class="topbar-title">{{ $pageTitle }}</div>

  @if($bmsCanAllBranches)
    <form method="POST" action="{{ route('bms.branch') }}" id="branch-form" style="display:inline;">
      @csrf
      <select class="branch-select" name="branch" onchange="document.getElementById('branch-form').submit()">
        <option value="all" {{ $bmsBranch === 'all' ? 'selected' : '' }}>All Branches</option>
        @foreach($bmsBranches as $br)
          <option value="{{ $br }}" {{ $bmsBranch === $br ? 'selected' : '' }}>{{ $br }}</option>
        @endforeach
      </select>
    </form>
  @endif

  <button class="btn-topbar" onclick="toggleTheme()" id="theme-btn" title="Toggle theme">{{ ($bmsSettings['theme'] ?? 'dark') === 'light' ? '🌙' : '☀' }}</button>

  @php $unreadNotifs = isset($bmsUser) ? \App\Models\BmsNotification::where('user_id', $bmsUser->id ?? 0)->whereNull('read_at')->count() : 0; @endphp
  <a href="{{ route('bms.notifications.index') }}" class="notif-btn" title="Notifications">
    🔔@if($unreadNotifs > 0)<span class="notif-badge">{{ $unreadNotifs }}</span>@endif
  </a>

  <form method="POST" action="{{ route('bms.logout') }}" style="display:inline;">
    @csrf
    <button type="submit" class="btn-logout">Sign Out</button>
  </form>
</div>

<div id="main">
  @if(session('success'))
    <div class="alert alert-success" id="flash-msg">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger" id="flash-msg">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
      </ul>
    </div>
  @endif

  @yield('content')
</div>

<div class="mobile-nav">
  <a href="{{ route('bms.dashboard') }}" class="{{ request()->routeIs('bms.dashboard') ? 'active' : '' }}"><span class="ico">📊</span><span>Home</span></a>
  <a href="{{ route('bms.jobs.index') }}" class="{{ request()->routeIs('bms.jobs.*') ? 'active' : '' }}"><span class="ico">🧾</span><span>Jobs</span></a>
  <a href="{{ route('bms.kanban') }}" class="{{ request()->routeIs('bms.kanban') ? 'active' : '' }}"><span class="ico">🗂</span><span>Flow</span></a>
  <a href="{{ route('bms.saleslog.index') }}" class="{{ request()->routeIs('bms.saleslog*') ? 'active' : '' }}"><span class="ico">💰</span><span>Sales</span></a>
  <a href="{{ route('bms.notifications.index') }}" class="{{ request()->routeIs('bms.notifications*') ? 'active' : '' }}"><span class="ico">🔔</span><span>Alerts</span></a>
</div>

<div id="toast"></div>

{{-- Page navigation progress bar --}}
<div id="page-loader"><div id="page-loader-bar"></div></div>

<script>
function toast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `toast-item toast-${type}`;
  el.textContent = msg;
  document.getElementById('toast').appendChild(el);
  setTimeout(() => { el.classList.add('leaving'); setTimeout(() => el.remove(), 300); }, 3000);
}
function toggleTheme() {
  const body = document.body;
  const isLight = body.classList.toggle('theme-light');
  const btn = document.getElementById('theme-btn');
  if (btn) btn.textContent = isLight ? '🌙' : '☀';
  const logo = document.getElementById('bms-brand-logo');
  if (logo) {
    const dark = logo.dataset.logoDark;
    const light = logo.dataset.logoLight;
    logo.src = isLight ? (light || dark) : (dark || light);
  }
  fetch('{{ route('bms.settings.theme') }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body: JSON.stringify({ theme: isLight ? 'light' : 'dark' })
  });
}
// Page navigation loader
(function() {
  var bar  = document.getElementById('page-loader-bar');
  var tid  = null;
  function start() {
    clearTimeout(tid);
    bar.style.transition = 'none';
    bar.style.width = '0%';
    requestAnimationFrame(function() {
      bar.style.transition = 'width 10s cubic-bezier(.1,.7,.1,1)';
      bar.style.width = '85%';
    });
  }
  function done() {
    bar.style.transition = 'width .2s ease';
    bar.style.width = '100%';
    tid = setTimeout(function() {
      bar.style.transition = 'opacity .3s';
      bar.style.opacity = '0';
      setTimeout(function() { bar.style.width='0%'; bar.style.opacity='1'; }, 300);
    }, 200);
  }
  document.addEventListener('click', function(e) {
    var a = e.target.closest('a');
    if (a && a.href && !a.target && !a.href.startsWith('javascript') && !a.href.startsWith('#')
        && a.hostname === location.hostname) {
      start();
    }
  });
  document.addEventListener('submit', function(e) {
    if (!e.target.closest('[data-no-loader]')) start();
  });
  window.addEventListener('pageshow', done);
})();

// Auto-dismiss flash
const fl = document.getElementById('flash-msg');
if (fl) setTimeout(() => { fl.style.opacity = '0'; fl.style.transition = 'opacity .5s'; setTimeout(() => fl.remove(), 500); }, 4000);
// Mobile sidebar toggle
window.closeSidebar = function () {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-backdrop').classList.remove('open');
};
document.addEventListener('DOMContentLoaded', () => {
  const topbar   = document.getElementById('topbar');
  const sidebar  = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');

  if (window.innerWidth <= 768 && topbar) {
    const btn = document.createElement('button');
    btn.innerHTML = '☰';
    btn.className = 'btn-topbar';
    btn.style.marginRight = '4px';
    btn.onclick = () => {
      sidebar.classList.toggle('open');
      backdrop.classList.toggle('open');
    };
    topbar.insertBefore(btn, topbar.firstChild);
  }

  // Tap backdrop → close
  backdrop.addEventListener('click', closeSidebar);

  // Tap any nav link → close (navigation away)
  sidebar.querySelectorAll('.nav-item').forEach(a => {
    a.addEventListener('click', () => {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });
});
</script>
@stack('scripts')
</body>
</html>
