{{-- Shared base styles for all PDF documents --}}
<style>
@page { margin: 0; size: A4 portrait; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #111; background: #fff; }
.doc-wrap { max-width: none; margin: 0; padding: 1.5cm; }

/* Header */
.doc-header { display: table; width: 100%; margin-bottom: 28px; table-layout: fixed; }
.doc-header-left { display: table-cell; vertical-align: top; width: 62%; }
.doc-header-right { display: table-cell; vertical-align: top; text-align: right; width: 38%; padding-left: 12px; }
.doc-company-name { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
.doc-company-sub { font-size: 11px; color: #666; margin-top: 3px; line-height: 1.6; }
.doc-ref { font-size: 22px; font-weight: 700; font-family: monospace; }
.doc-ref-sub { font-size: 11px; color: #666; margin-top: 3px; line-height: 1.6; }

/* Divider */
.doc-divider { border: none; border-top: 2px solid #ddd; margin: 18px 0; }
.doc-divider-accent { border-color: var(--accent-color, #b91c1c); }

/* Info grid */
.info-grid { display: table; width: 100%; margin-bottom: 24px; }
.info-col { display: table-cell; width: 50%; vertical-align: top; }
.info-col + .info-col { padding-left: 24px; }
.info-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 6px; }
.info-value { font-size: 13px; line-height: 1.7; }
.info-value strong { font-weight: 700; }

/* Tables */
table { width: 100%; border-collapse: collapse; }
thead th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; padding: 8px 10px; border-bottom: 2px solid #e5e7eb; text-align: left; }
tbody td { font-size: 13px; padding: 10px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
tbody tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }
.mono { font-family: monospace; }

/* Totals */
.totals-wrap { margin-top: 16px; }
.totals-table { width: 260px; border-collapse: collapse; margin-left: auto; }
.totals-table td { padding: 5px 2px; font-size: 13px; border: none; }
.totals-table td:last-child { text-align: right; font-family: monospace; }
.totals-table .total-final td { border-top: 2px solid #111; font-weight: 700; font-size: 15px; padding-top: 8px; }

/* Badges */
.badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; }
.badge-paid { background: #d1fae5; color: #065f46; }
.badge-unpaid { background: #fee2e2; color: #991b1b; }
.badge-draft { background: #e5e7eb; color: #374151; }
.badge-sent { background: #dbeafe; color: #1d4ed8; }
.badge-accepted { background: #d1fae5; color: #065f46; }

/* Footer */
.doc-footer { margin-top: 36px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #999; text-align: center; line-height: 1.8; }

/* Report-specific */
.kpi-row { display: table; width: 100%; margin-bottom: 24px; }
.kpi-cell { display: table-cell; text-align: center; padding: 16px 12px; border: 1px solid #e5e7eb; }
.kpi-cell + .kpi-cell { border-left: none; }
.kpi-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; }
.kpi-value { font-size: 20px; font-weight: 700; margin-top: 4px; }
.kpi-sub { font-size: 11px; color: #888; margin-top: 2px; }

.section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #666; margin: 20px 0 10px; }
.bar-row { display: table; width: 100%; margin-bottom: 6px; }
.bar-label { display: table-cell; font-size: 12px; width: 130px; vertical-align: middle; }
.bar-track { display: table-cell; vertical-align: middle; padding-left: 8px; }
.bar-fill { height: 8px; border-radius: 4px; background: #b91c1c; }
.bar-count { display: table-cell; font-size: 12px; text-align: right; width: 60px; vertical-align: middle; color: #666; }

.page-break { page-break-after: always; }
</style>
