<?php

namespace App\Http\Controllers\Bms;

use App\Models\Opex;
use App\Models\Payroll;
use App\Models\PrintJob;
use App\Models\SalesLog;
use App\Support\BmsSettingsDefaults;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends BmsController
{
    public function download(Request $request, string $type): Response
    {
        $this->authorizeBms('settings', 'read');

        $settings = $this->bmsSettings();
        $branch   = $this->branchFilter();
        $from     = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to       = $request->query('to')   ? Carbon::parse($request->query('to'))->endOfDay()     : now()->endOfDay();

        $branchLabel = $branch ?? 'All Branches';
        $periodLabel = $from->format('d M Y').' – '.$to->format('d M Y');

        [$reportTitle, $kpis, $sections] = match ($type) {
            'sales'     => $this->salesReport($from, $to, $branch, $settings),
            'jobs'      => $this->jobsReport($from, $to, $branch, $settings),
            'finance'   => $this->financeReport($from, $to, $branch, $settings),
            'vat'       => $this->vatReport($from, $to, $branch, $settings),
            default     => $this->salesReport($from, $to, $branch, $settings),
        };

        $pdf = Pdf::loadView('pdf.report', compact(
            'settings', 'reportTitle', 'kpis', 'sections', 'periodLabel', 'branchLabel'
        ))->setPaper('a4', 'portrait');

        $filename = strtolower(str_replace(' ', '-', $reportTitle)).'-'.now()->format('Ymd').'.pdf';

        return $pdf->stream($filename);
    }

    // ── Report builders ────────────────────────────────────────────────────

    private function salesReport(Carbon $from, Carbon $to, ?string $branch, array $settings): array
    {
        $symbol = $settings['currency_symbol'] ?? 'KSh';

        $jobs = PrintJob::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $salesLogs = SalesLog::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $revenue  = $jobs->sum('amount') + $salesLogs->sum('amount');
        $paid     = $jobs->where('paid', true)->sum('amount');
        $outstanding = $jobs->where('paid', false)->sum('amount');

        $kpis = [
            ['label' => 'Total Revenue',    'value' => $symbol.' '.number_format($revenue),     'sub' => 'Jobs + Sales Log'],
            ['label' => 'Collected',         'value' => $symbol.' '.number_format($paid),         'sub' => 'Paid jobs', 'color' => '#16a34a'],
            ['label' => 'Outstanding',       'value' => $symbol.' '.number_format($outstanding),  'sub' => 'Unpaid balance', 'color' => '#d97706'],
            ['label' => 'Total Transactions','value' => $jobs->count() + $salesLogs->count(),    'sub' => 'Jobs + log entries'],
        ];

        // By category
        $byCategory = $jobs->groupBy('category')
            ->map(fn($g) => ['count' => $g->count(), 'amount' => $g->sum('amount')])
            ->sortByDesc('amount');

        $categoryRows = $byCategory->map(fn($v, $k) => [
            'category' => $k ?: 'Uncategorised',
            'count'    => $v['count'],
            'amount'   => $symbol.' '.number_format($v['amount']),
        ])->values()->all();

        // By branch
        $byBranch = $jobs->groupBy('branch')
            ->map(fn($g) => $g->sum('amount'))
            ->sortDesc();

        $branchRows = $byBranch->map(fn($amt, $name) => [
            'label'   => $name ?: 'Unknown',
            'value'   => $amt,
            'display' => $symbol.' '.number_format($amt),
        ])->values()->all();

        // Monthly trend
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $m   = $cursor->format('Y-m');
            $amt = $jobs->filter(fn($j) => $j->created_at && $j->created_at->format('Y-m') === $m)->sum('amount');
            $months[] = [
                'month'   => $cursor->format('M Y'),
                'jobs'    => $jobs->filter(fn($j) => $j->created_at && $j->created_at->format('Y-m') === $m)->count(),
                'revenue' => $symbol.' '.number_format($amt),
            ];
            $cursor->addMonth();
        }

        $sections = [
            ['title' => 'Revenue by Category', 'type' => 'table', 'columns' => [
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'count',    'label' => 'Jobs',   'right' => true],
                ['key' => 'amount',   'label' => 'Revenue','right' => true, 'bold' => true],
            ], 'rows' => $categoryRows],
            ['title' => 'Revenue by Branch', 'type' => 'bars', 'rows' => $branchRows],
            ['title' => 'Monthly Trend', 'type' => 'table', 'columns' => [
                ['key' => 'month',   'label' => 'Month'],
                ['key' => 'jobs',    'label' => 'Jobs',   'right' => true],
                ['key' => 'revenue', 'label' => 'Revenue','right' => true, 'bold' => true],
            ], 'rows' => $months],
        ];

        return ['Sales Summary Report', $kpis, $sections];
    }

    private function jobsReport(Carbon $from, Carbon $to, ?string $branch, array $settings): array
    {
        $symbol = $settings['currency_symbol'] ?? 'KSh';

        $jobs = PrintJob::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $stages    = ['waiting', 'designing', 'approval', 'printing', 'fabrication', 'ready', 'installed', 'paid'];
        $byStage   = $jobs->groupBy('stage');
        $byPriority = $jobs->groupBy('priority');

        $kpis = [
            ['label' => 'Total Jobs',   'value' => $jobs->count(),                             'sub' => 'In period'],
            ['label' => 'Completed',    'value' => $byStage->get('paid', collect())->count(),  'sub' => 'Paid/installed', 'color' => '#16a34a'],
            ['label' => 'In Progress',  'value' => $jobs->whereNotIn('stage', ['paid','installed'])->count(), 'sub' => 'Active jobs'],
            ['label' => 'Revenue',      'value' => $symbol.' '.number_format($jobs->sum('amount')), 'sub' => 'Billed total'],
        ];

        $stageRows = collect($stages)->map(fn($s) => [
            'label'   => ucfirst($s),
            'value'   => $byStage->get($s, collect())->count(),
            'display' => $byStage->get($s, collect())->count().' jobs',
        ])->filter(fn($r) => $r['value'] > 0)->values()->all();

        $priorityRows = [
            ['label' => 'HIGH',   'value' => $byPriority->get('high', collect())->count(),   'display' => $byPriority->get('high', collect())->count()],
            ['label' => 'MEDIUM', 'value' => $byPriority->get('medium', collect())->count(), 'display' => $byPriority->get('medium', collect())->count()],
            ['label' => 'LOW',    'value' => $byPriority->get('low', collect())->count(),    'display' => $byPriority->get('low', collect())->count()],
        ];

        $recentRows = $jobs->sortByDesc('created_at')->take(20)->map(fn($j) => [
            'id'       => $j->id,
            'title'    => \Illuminate\Support\Str::limit($j->title ?? '—', 35),
            'branch'   => $j->branch,
            'stage'    => ucfirst($j->stage ?? '—'),
            'amount'   => $symbol.' '.number_format($j->amount, 2),
        ])->values()->all();

        $sections = [
            ['title' => 'Jobs by Stage',    'type' => 'bars',  'rows' => $stageRows],
            ['title' => 'Jobs by Priority', 'type' => 'bars',  'rows' => $priorityRows],
            ['title' => 'Job Listing (latest 20)', 'type' => 'table', 'columns' => [
                ['key' => 'id',     'label' => 'Job ID'],
                ['key' => 'title',  'label' => 'Title'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'stage',  'label' => 'Stage'],
                ['key' => 'amount', 'label' => 'Amount', 'right' => true, 'bold' => true],
            ], 'rows' => $recentRows],
        ];

        return ['Job Status Report', $kpis, $sections];
    }

    private function financeReport(Carbon $from, Carbon $to, ?string $branch, array $settings): array
    {
        $symbol = $settings['currency_symbol'] ?? 'KSh';

        $revenue = PrintJob::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $expenses = 0;
        $payroll  = 0;
        if (class_exists(Opex::class)) {
            $expenses = Opex::query()->when($branch, fn($q) => $q->where('branch', $branch))
                ->whereBetween('date', [$from, $to])->sum('amount');
        }
        if (class_exists(Payroll::class)) {
            $payroll = Payroll::query()->when($branch, fn($q) => $q->where('branch', $branch))
                ->whereBetween('date', [$from, $to])->sum('gross');
        }

        $net = $revenue - $expenses - $payroll;

        $kpis = [
            ['label' => 'Revenue',  'value' => $symbol.' '.number_format($revenue),  'sub' => 'Billed jobs'],
            ['label' => 'Expenses', 'value' => $symbol.' '.number_format($expenses), 'sub' => 'Opex', 'color' => '#dc2626'],
            ['label' => 'Payroll',  'value' => $symbol.' '.number_format($payroll),  'sub' => 'Staff costs', 'color' => '#dc2626'],
            ['label' => 'Net',      'value' => $symbol.' '.number_format($net),      'sub' => 'After costs', 'color' => $net >= 0 ? '#16a34a' : '#dc2626'],
        ];

        $sections = [
            ['title' => 'Financial Summary', 'type' => 'table', 'columns' => [
                ['key' => 'item',   'label' => 'Line Item'],
                ['key' => 'amount', 'label' => 'Amount ('.$symbol.')', 'right' => true, 'bold' => true],
            ], 'rows' => [
                ['item' => 'Gross Revenue',    'amount' => number_format($revenue, 2)],
                ['item' => 'Operating Expenses','amount' => '('.number_format($expenses, 2).')'],
                ['item' => 'Payroll Costs',     'amount' => '('.number_format($payroll, 2).')'],
                ['item' => 'Net Position',      'amount' => number_format($net, 2)],
            ]],
        ];

        return ['Financial Overview Report', $kpis, $sections];
    }

    private function vatReport(Carbon $from, Carbon $to, ?string $branch, array $settings): array
    {
        $symbol  = $settings['currency_symbol'] ?? 'KSh';
        $vatRate = (float)($settings['vat_rate'] ?? 16);

        $jobs = PrintJob::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $gross  = $jobs->sum('amount');
        $net    = $gross / (1 + $vatRate / 100);
        $vat    = $gross - $net;

        $kpis = [
            ['label' => 'Gross Revenue',       'value' => $symbol.' '.number_format($gross),   'sub' => 'VAT inclusive'],
            ['label' => 'Net Revenue',          'value' => $symbol.' '.number_format($net),    'sub' => 'Ex-VAT'],
            ['label' => 'Output VAT ('.$vatRate.'%)', 'value' => $symbol.' '.number_format($vat), 'sub' => 'Payable to KRA', 'color' => '#d97706'],
        ];

        // Monthly breakdown
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $m       = $cursor->format('Y-m');
            $mGross  = $jobs->filter(fn($j) => $j->created_at && $j->created_at->format('Y-m') === $m)->sum('amount');
            $mNet    = $mGross / (1 + $vatRate / 100);
            $mVat    = $mGross - $mNet;
            if ($mGross > 0) {
                $months[] = [
                    'month' => $cursor->format('M Y'),
                    'gross' => $symbol.' '.number_format($mGross, 2),
                    'net'   => $symbol.' '.number_format($mNet, 2),
                    'vat'   => $symbol.' '.number_format($mVat, 2),
                ];
            }
            $cursor->addMonth();
        }

        $sections = [
            ['title' => 'VAT Summary', 'type' => 'table', 'columns' => [
                ['key' => 'item',   'label' => 'Description'],
                ['key' => 'amount', 'label' => 'Amount ('.$symbol.')', 'right' => true, 'bold' => true],
            ], 'rows' => [
                ['item' => 'Gross Revenue (incl. VAT)',     'amount' => number_format($gross, 2)],
                ['item' => 'Net Revenue (ex-VAT)',           'amount' => number_format($net, 2)],
                ['item' => 'Output VAT ('.$vatRate.'%)',     'amount' => number_format($vat, 2)],
            ]],
            ['title' => 'Monthly VAT Breakdown', 'type' => 'table', 'columns' => [
                ['key' => 'month', 'label' => 'Month'],
                ['key' => 'gross', 'label' => 'Gross',  'right' => true],
                ['key' => 'net',   'label' => 'Net',    'right' => true],
                ['key' => 'vat',   'label' => 'VAT',    'right' => true, 'bold' => true],
            ], 'rows' => $months, 'totals' => [
                'month' => 'TOTAL',
                'gross' => $symbol.' '.number_format($gross, 2),
                'net'   => $symbol.' '.number_format($net, 2),
                'vat'   => $symbol.' '.number_format($vat, 2),
            ]],
        ];

        return ['VAT Report', $kpis, $sections];
    }
}
