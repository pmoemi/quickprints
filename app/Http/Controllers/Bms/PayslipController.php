<?php

namespace App\Http\Controllers\Bms;

use App\Models\PayrollEntry;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayslipController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('payroll', 'read');
        $month = $request->get('month', now()->format('Y-m'));
        $payroll = PayrollEntry::query()
            ->where('month', 'like', $month.'%')
            ->orderByDesc('id')
            ->get();
        $staff = Staff::query()->orderBy('name')->get()->keyBy('id');

        return view('payslips.index', compact('payroll', 'staff', 'month'));
    }
}


