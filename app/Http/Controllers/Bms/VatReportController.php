<?php

namespace App\Http\Controllers\Bms;

use App\Models\PrintJob;
use App\Models\SalesLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VatReportController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('ledger', 'read');
        $month = $request->get('month', now()->format('Y-m'));
        $vatRate = (float) ($this->bmsSettings()['vat_rate'] ?? 16);

        $sales = (float) $this->scopeBranch(SalesLog::query())
            ->where('date', 'like', $month.'%')
            ->sum('amount');
        $jobs = (float) $this->scopeBranch(PrintJob::query())
            ->where('paid', true)
            ->where('updated_at', 'like', $month.'%')
            ->sum('amount');

        $taxable = $sales + $jobs;
        $vat = $taxable * ($vatRate / (100 + $vatRate));
        $net = $taxable - $vat;

        return view('vat.index', compact('month', 'taxable', 'vat', 'net', 'vatRate', 'sales', 'jobs'));
    }
}


