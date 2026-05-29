<?php

namespace App\Http\Controllers\Bms;

use App\Models\SalesLog;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommissionController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('saleslog', 'read');
        $month = $request->get('month', now()->format('Y-m'));
        $query = $this->scopeBranch(SalesLog::query())
            ->with('salesRep')
            ->where('date', 'like', $month.'%');

        $currentStaffId = $this->currentStaffId();
        $role = Auth::user()?->role;
        if ($currentStaffId && ! in_array($role, ['Admin', 'General Manager', 'Operations Manager'], true)) {
            $query->where('sales_rep_id', $currentStaffId);
        }

        $logs = $query->get();
        $staff = Staff::query()->where('active', true)->get()->keyBy('id');
        $rate = (float) ($this->bmsSettings()['commission_rate'] ?? 5);

        $rows = [];
        foreach ($logs->groupBy('sales_rep_id') as $repId => $group) {
            $total = $group->sum('amount');
            $repName = $staff[$repId]->name
                ?? $group->first()->salesRep?->name
                ?? $group->first()->logged_by
                ?? 'Unknown';
            $rows[] = [
                'rep' => $repName,
                'sales' => $total,
                'commission' => $total * ($rate / 100),
                'deals' => $group->count(),
            ];
        }
        usort($rows, fn ($a, $b) => $b['sales'] <=> $a['sales']);

        return view('commissions.index', compact('rows', 'month', 'rate'));
    }
}


