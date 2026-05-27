<?php

namespace App\Http\Controllers\Bms;

use App\Models\SalesLog;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesTargetController extends BmsController
{
    public function index(Request $request): View
    {
        $this->authorizeBms('saleslog', 'read');
        $month = $request->get('month', now()->format('Y-m'));
        $targets = $this->bmsSettings()['sales_targets'] ?? [];
        $actual = (float) $this->scopeBranch(SalesLog::query())
            ->where('date', 'like', $month.'%')
            ->sum('amount');
        $target = (float) ($targets[$month] ?? $targets['default'] ?? 500000);

        return view('sales-targets.index', compact('month', 'actual', 'target', 'targets'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeBms('settings', 'update');
        $data = $request->validate([
            'month' => 'required|string|max:7',
            'target' => 'required|numeric|min:0',
        ]);
        $settings = $this->bmsSettings();
        $targets = $settings['sales_targets'] ?? [];
        $targets[$data['month']] = (float) $data['target'];
        $this->settings->update(['sales_targets' => $targets]);
        BmsAudit::log('Updated sales target for '.$data['month']);

        return redirect()->route('bms.sales-targets.index', ['month' => $data['month']])->with('success', 'Target saved.');
    }
}


