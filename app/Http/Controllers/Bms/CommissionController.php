<?php

namespace App\Http\Controllers\Bms;

use App\Models\SalesLog;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('saleslog', 'read');
        $month = $request->get('month', now()->format('Y-m'));
        $logs = $this->scopeBranch(SalesLog::query())
            ->where('date', 'like', $month.'%')
            ->get();
        $staff = Staff::query()->where('active', true)->get()->keyBy('id');
        $rate = (float) ($this->bmsSettings()['commission_rate'] ?? 5);

        $rows = [];
        foreach ($logs->groupBy('sales_rep_id') as $repId => $group) {
            $total = $group->sum('amount');
            $rows[] = [
                'rep' => $staff[$repId]->name ?? ($group->first()->sales_rep ?? 'Unknown'),
                'sales' => $total,
                'commission' => $total * ($rate / 100),
                'deals' => $group->count(),
            ];
        }
        usort($rows, fn ($a, $b) => $b['sales'] <=> $a['sales']);

        return view('commissions.index', compact('rows', 'month', 'rate'));
    }
}


