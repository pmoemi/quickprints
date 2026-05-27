<?php

namespace App\Http\Controllers\Bms;

use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\PrintJob;
use App\Models\SalesLog;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('jobs', 'read');

        $jobs = $this->scopeBranch(PrintJob::query())->get();
        $clients = Client::query()->get()->keyBy('id');
        $salesLog = $this->scopeBranch(SalesLog::query())->get();
        $inventory = $this->scopeBranch(InventoryItem::query())->get();

        $today = now()->toDateString();
        $branches = $this->branchNames();

        $branchStats = collect($branches)->map(function (string $branch) use ($jobs) {
            $bj = $jobs->where('branch', $branch);

            return [
                'name' => $branch,
                'jobs' => $bj->count(),
                'revenue' => $bj->where('paid', true)->sum('amount'),
            ];
        });

        // Last 30 days daily revenue from sales log
        $start = Carbon::today()->subDays(29);
        $revenueByDay = collect();
        for ($d = 0; $d < 30; $d++) {
            $date = $start->copy()->addDays($d)->toDateString();
            $revenueByDay[$date] = (float) $salesLog
                ->filter(fn ($s) => optional($s->date)->toDateString() === $date)
                ->sum('amount');
        }

        // Jobs by category
        $jobsByCategory = $jobs->groupBy('category')
            ->map(fn ($g) => $g->count())
            ->sortByDesc(fn ($v) => $v)
            ->take(8);

        // Jobs by category revenue (paid)
        $revenueByCategory = $jobs->where('paid', true)
            ->groupBy('category')
            ->map(fn ($g) => (float) $g->sum('amount'))
            ->sortByDesc(fn ($v) => $v)
            ->take(8);

        $yesterday = Carbon::yesterday()->toDateString();
        $weekStart = Carbon::today()->subDays(6);
        $prevWeekStart = Carbon::today()->subDays(13);
        $prevWeekEnd = Carbon::today()->subDays(7);

        $todaySales = (float) $salesLog->filter(fn ($s) => optional($s->date)->toDateString() === $today)->sum('amount');
        $yesterdaySales = (float) $salesLog->filter(fn ($s) => optional($s->date)->toDateString() === $yesterday)->sum('amount');
        $weekRevenue = (float) $salesLog->filter(fn ($s) => optional($s->date)->gte($weekStart))->sum('amount');
        $prevWeekRevenue = (float) $salesLog->filter(fn ($s) => optional($s->date)->between($prevWeekStart, $prevWeekEnd))->sum('amount');
        $monthRevenue = (float) $revenueByDay->sum();

        return view('dashboard', [
            'jobs' => $jobs,
            'salesLog' => $salesLog,
            'inventory' => $inventory,
            'today' => $today,
            'branchStats' => $branchStats,
            'settings' => $this->bmsSettings(),
            'revenueByDay' => $revenueByDay,
            'jobsByCategory' => $jobsByCategory,
            'revenueByCategory' => $revenueByCategory,
            'todaySales' => $todaySales,
            'yesterdaySales' => $yesterdaySales,
            'weekRevenue' => $weekRevenue,
            'prevWeekRevenue' => $prevWeekRevenue,
            'monthRevenue' => $monthRevenue,
            'clients' => $clients,
        ]);
    }
}
