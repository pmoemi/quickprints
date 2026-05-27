<?php

namespace App\Http\Controllers\Bms;

use App\Models\OpexEntry;
use App\Models\PayrollEntry;
use App\Models\PrintJob;
use App\Models\SalesLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BoardController extends BmsController
{
    public function designer(): View
    {
        $jobs = $this->scopeBranch(PrintJob::query())
            ->whereIn('stage', ['waiting', 'designing', 'approval'])
            ->orderBy('deadline')
            ->get();

        return view('boards/designer', [
            'title' => 'Designer Board',
            'jobs' => $jobs,
            'clients' => $this->scopedClientsKeyBy(),
        ]);
    }

    public function operator(): View
    {
        $jobs = $this->scopeBranch(PrintJob::query())
            ->whereIn('stage', ['printing', 'fabrication', 'ready'])
            ->orderBy('deadline')
            ->get();

        return view('boards/operator', [
            'title' => 'Operator Queue',
            'jobs' => $jobs,
            'clients' => $this->scopedClientsKeyBy(),
        ]);
    }

    public function fabrication(): View
    {
        $jobs = $this->scopeBranch(PrintJob::query())
            ->where('stage', 'fabrication')
            ->orderBy('deadline')
            ->get();

        return view('boards/fabrication', [
            'title' => 'Fabrication Queue',
            'jobs' => $jobs,
            'clients' => $this->scopedClientsKeyBy(),
        ]);
    }

    public function delivery(): View
    {
        $jobs = $this->scopeBranch(PrintJob::query())
            ->whereIn('stage', ['ready', 'installed'])
            ->orderBy('deadline')
            ->get();

        return view('boards/delivery', [
            'jobs' => $jobs,
            'clients' => $this->scopedClientsKeyBy(),
        ]);
    }

    public function followups(): View
    {
        $this->authorizeBms('leads', 'read');
        $leads = $this->scopeBranch(\App\Models\Lead::query())->get();

        return view('followups', compact('leads'));
    }

    public function payments(): View
    {
        $this->authorizeBms('jobs', 'read');
        $jobs = $this->scopeBranch(PrintJob::query())->orderByDesc('id')->get();
        $clients = $this->scopedClientsKeyBy();

        return view('payments', compact('jobs', 'clients'));
    }

    public function reports(Request $request): View
    {
        $this->authorizeBms('reports', 'read');

        $settings = $this->bmsSettings();
        $type     = $request->input('type', 'sales');
        $from     = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to       = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();
        $branches = $this->branchNames();
        $symbol   = $settings['currency_symbol'] ?? 'KSh';
        $vatRate  = (float) ($settings['vat_rate'] ?? 16);

        $jobs = $this->scopeBranch(PrintJob::query())
            ->whereBetween('created_at', [$from, $to])
            ->get();

        [$kpis, $sections] = match ($type) {
            'jobs'    => $this->rptJobs($jobs, $symbol),
            'finance' => $this->rptFinance($jobs, $from, $to, $symbol),
            'vat'     => $this->rptVat($jobs, $from, $to, $symbol, $vatRate),
            default   => $this->rptSales($jobs, $from, $to, $symbol),
        };

        return view('reports', compact('type', 'from', 'to', 'branches', 'kpis', 'sections', 'settings', 'symbol'));
    }

    public function advanceStage(Request $request, string $id): RedirectResponse
    {
        $this->authorizeBms('jobs', 'update');
        $job = PrintJob::query()->findOrFail($id);
        $stage = $request->validate(['stage' => 'required|string'])['stage'];

        $history = $job->history ?? [];
        $history[] = ['action' => "Stage → {$stage}", 'by' => $request->user()->name, 'at' => now()->toIso8601String()];
        $job->update(['stage' => $stage, 'history' => $history]);

        return back()->with('success', 'Stage updated.');
    }

    // ── Report builders ────────────────────────────────────────────────────

    private function rptSales(Collection $jobs, Carbon $from, Carbon $to, string $symbol): array
    {
        $branch    = $this->branchFilter();
        $salesLogs = SalesLog::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $revenue     = (float) $jobs->sum('amount') + (float) $salesLogs->sum('amount');
        $paid        = (float) $jobs->where('paid', true)->sum('amount');
        $outstanding = (float) $jobs->where('paid', false)->sum('amount');

        $kpis = [
            ['label' => 'Total Revenue', 'value' => $symbol.' '.number_format($revenue),    'sub' => 'Jobs + Sales Log'],
            ['label' => 'Collected',      'value' => $symbol.' '.number_format($paid),        'sub' => 'Paid jobs',      'color' => '#16a34a'],
            ['label' => 'Outstanding',    'value' => $symbol.' '.number_format($outstanding), 'sub' => 'Unpaid balance', 'color' => '#d97706'],
            ['label' => 'Transactions',   'value' => $jobs->count() + $salesLogs->count(),    'sub' => 'Jobs + log entries'],
        ];

        $categoryRows = $jobs->groupBy('category')
            ->map(fn($g) => ['count' => $g->count(), 'amount' => (float) $g->sum('amount')])
            ->sortByDesc('amount')
            ->map(fn($v, $k) => [
                'category' => $k ?: 'Uncategorised',
                'count'    => $v['count'],
                'amount'   => $symbol.' '.number_format($v['amount']),
            ])->values()->all();

        $months  = [];
        $cursor  = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $m     = $cursor->format('Y-m');
            $mJobs = $jobs->filter(fn($j) => $j->created_at?->format('Y-m') === $m);
            $amt   = (float) $mJobs->sum('amount');
            $months[] = [
                'month'   => $cursor->format('M Y'),
                'jobs'    => $mJobs->count(),
                'value'   => $amt,
                'display' => number_format($amt),
            ];
            $cursor->addMonth();
        }

        $sections = [
            ['title' => 'Revenue by Category', 'type' => 'table', 'columns' => [
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'count',    'label' => 'Jobs',    'right' => true],
                ['key' => 'amount',   'label' => 'Revenue', 'right' => true, 'bold' => true],
            ], 'rows' => $categoryRows],
            ['title' => 'Monthly Revenue Trend', 'type' => 'monthly', 'rows' => $months],
        ];

        return [$kpis, $sections];
    }

    private function rptJobs(Collection $jobs, string $symbol): array
    {
        $stages     = ['waiting', 'designing', 'approval', 'printing', 'fabrication', 'ready', 'installed', 'paid'];
        $byStage    = $jobs->groupBy('stage');
        $byPriority = $jobs->groupBy('priority');

        $kpis = [
            ['label' => 'Total Jobs',   'value' => $jobs->count(),                                             'sub' => 'In period'],
            ['label' => 'Completed',    'value' => $byStage->get('paid', collect())->count(),                  'sub' => 'Paid/installed', 'color' => '#16a34a'],
            ['label' => 'In Progress',  'value' => $jobs->whereNotIn('stage', ['paid', 'installed'])->count(), 'sub' => 'Active jobs'],
            ['label' => 'Revenue',      'value' => $symbol.' '.number_format($jobs->sum('amount')),            'sub' => 'Billed total'],
        ];

        $stageRows = collect($stages)
            ->map(fn($s) => [
                'label' => ucfirst($s),
                'count' => $byStage->get($s, collect())->count(),
                'value' => $byStage->get($s, collect())->count(),
            ])
            ->filter(fn($r) => $r['count'] > 0)
            ->values()->all();

        $priorityRows = collect(['high', 'medium', 'low'])
            ->map(fn($p) => [
                'label' => strtoupper($p),
                'count' => $byPriority->get($p, collect())->count(),
                'value' => $byPriority->get($p, collect())->count(),
                'color' => $p === 'high' ? '#dc2626' : ($p === 'medium' ? '#d97706' : '#16a34a'),
            ])->all();

        $recentRows = $jobs->sortByDesc('created_at')->take(20)->map(fn($j) => [
            'id'     => $j->id,
            'title'  => \Illuminate\Support\Str::limit($j->title ?? '—', 35),
            'branch' => $j->branch ?? '—',
            'stage'  => ucfirst($j->stage ?? '—'),
            'amount' => $symbol.' '.number_format($j->amount, 2),
        ])->values()->all();

        $sections = [
            ['title' => 'Jobs by Stage',    'type' => 'bars',  'rows' => $stageRows],
            ['title' => 'Jobs by Priority', 'type' => 'bars',  'rows' => $priorityRows],
            ['title' => 'Recent Jobs (latest 20)', 'type' => 'table', 'columns' => [
                ['key' => 'id',     'label' => 'Job ID'],
                ['key' => 'title',  'label' => 'Title'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'stage',  'label' => 'Stage'],
                ['key' => 'amount', 'label' => 'Amount', 'right' => true, 'bold' => true],
            ], 'rows' => $recentRows],
        ];

        return [$kpis, $sections];
    }

    private function rptFinance(Collection $jobs, Carbon $from, Carbon $to, string $symbol): array
    {
        $branch  = $this->branchFilter();
        $revenue = (float) $jobs->sum('amount');

        $expenses = (float) OpexEntry::query()
            ->when($branch, fn($q) => $q->where('branch', $branch))
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $payroll = (float) PayrollEntry::query()
            ->whereBetween('created_at', [$from, $to])
            ->sum('gross_salary');

        $net = $revenue - $expenses - $payroll;

        $kpis = [
            ['label' => 'Revenue',       'value' => $symbol.' '.number_format($revenue),  'sub' => 'Billed jobs'],
            ['label' => 'Expenses',      'value' => $symbol.' '.number_format($expenses), 'sub' => 'Operating expenses', 'color' => '#dc2626'],
            ['label' => 'Payroll',       'value' => $symbol.' '.number_format($payroll),  'sub' => 'Staff costs',        'color' => '#dc2626'],
            ['label' => 'Net Position',  'value' => $symbol.' '.number_format($net),      'sub' => 'After all costs',    'color' => $net >= 0 ? '#16a34a' : '#dc2626'],
        ];

        $sections = [
            ['title' => 'Financial Summary', 'type' => 'table', 'columns' => [
                ['key' => 'item',   'label' => 'Line Item'],
                ['key' => 'amount', 'label' => 'Amount ('.$symbol.')', 'right' => true, 'bold' => true],
            ], 'rows' => [
                ['item' => 'Gross Revenue',      'amount' => number_format($revenue, 2)],
                ['item' => 'Operating Expenses', 'amount' => '('.number_format($expenses, 2).')'],
                ['item' => 'Payroll Costs',      'amount' => '('.number_format($payroll, 2).')'],
                ['item' => 'Net Position',       'amount' => number_format($net, 2)],
            ]],
        ];

        return [$kpis, $sections];
    }

    private function rptVat(Collection $jobs, Carbon $from, Carbon $to, string $symbol, float $vatRate): array
    {
        $gross = (float) $jobs->sum('amount');
        $net   = $vatRate > 0 ? $gross / (1 + $vatRate / 100) : $gross;
        $vat   = $gross - $net;

        $kpis = [
            ['label' => 'Gross Revenue',               'value' => $symbol.' '.number_format($gross), 'sub' => 'VAT inclusive'],
            ['label' => 'Net Revenue',                  'value' => $symbol.' '.number_format($net),  'sub' => 'Excluding VAT'],
            ['label' => 'Output VAT ('.$vatRate.'%)',   'value' => $symbol.' '.number_format($vat),  'sub' => 'Payable to KRA', 'color' => '#d97706'],
            ['label' => 'Jobs in Period',               'value' => $jobs->count(),                  'sub' => 'Transactions'],
        ];

        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $m      = $cursor->format('Y-m');
            $mGross = (float) $jobs->filter(fn($j) => $j->created_at?->format('Y-m') === $m)->sum('amount');
            if ($mGross > 0) {
                $mNet    = $vatRate > 0 ? $mGross / (1 + $vatRate / 100) : $mGross;
                $mVat    = $mGross - $mNet;
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
                ['item' => 'Gross Revenue (incl. VAT)', 'amount' => number_format($gross, 2)],
                ['item' => 'Net Revenue (ex-VAT)',       'amount' => number_format($net, 2)],
                ['item' => 'Output VAT ('.$vatRate.'%)', 'amount' => number_format($vat, 2)],
            ]],
            ['title' => 'Monthly VAT Breakdown', 'type' => 'table', 'columns' => [
                ['key' => 'month', 'label' => 'Month'],
                ['key' => 'gross', 'label' => 'Gross (incl. VAT)',  'right' => true],
                ['key' => 'net',   'label' => 'Net (ex-VAT)',       'right' => true],
                ['key' => 'vat',   'label' => 'Output VAT',         'right' => true, 'bold' => true],
            ], 'rows' => $months],
        ];

        return [$kpis, $sections];
    }
}
