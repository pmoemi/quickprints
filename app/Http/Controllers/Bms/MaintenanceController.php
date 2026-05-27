<?php

namespace App\Http\Controllers\Bms;

use App\Services\BmsDataResetService;
use App\Support\BmsAudit;
use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends BmsController
{
    public function index(BmsDataResetService $reset): View
    {
        $this->authorizeBms('settings', 'read');

        $counts = $reset->recordCounts();
        arsort($counts);

        return view('maintenance.index', [
            'recordCounts' => $counts,
            'totalRecords' => $reset->totalRecords(),
            'canResetData' => \App\Support\BmsPermissions::allowed(auth()->user()?->role, 'settings', 'delete'),
            'demoLogin' => DemoData::DEFAULT_ADMIN_EMAIL,
            'demoPassword' => DemoData::DEFAULT_ADMIN_PASSWORD,
        ]);
    }

    public function clearData(Request $request, BmsDataResetService $reset): RedirectResponse
    {
        $this->authorizeBms('settings', 'delete');
        $this->validateResetConfirmation($request);

        $includeAudit = ! $request->boolean('keep_audit');
        $removed = $reset->clearRecords($includeAudit);
        $total = array_sum($removed);

        BmsAudit::log("Cleared {$total} operational records via maintenance");

        return back()->with('success', "Cleared {$total} records. Settings and user accounts were kept.");
    }

    public function seedDemo(BmsDataResetService $reset): RedirectResponse
    {
        $this->authorizeBms('settings', 'delete');
        $reset->seedDemo();

        BmsAudit::log('Loaded demo sample data via maintenance');

        return back()->with('success', 'Demo data loaded. Sample jobs, clients, sales, and finance records are ready.');
    }

    public function resetDemo(Request $request, BmsDataResetService $reset): RedirectResponse
    {
        $this->authorizeBms('settings', 'delete');
        $this->validateResetConfirmation($request);

        $includeAudit = ! $request->boolean('keep_audit');
        $result = $reset->resetToDemo($includeAudit);
        $total = array_sum($result['cleared']);

        BmsAudit::log("Reset to demo: cleared {$total} records and reloaded sample data");

        return back()->with('success', "Reset complete — cleared {$total} records and loaded fresh demo data.");
    }

    private function validateResetConfirmation(Request $request): void
    {
        $request->validate([
            'confirmation' => 'required|in:RESET',
        ], [
            'confirmation.in' => 'Type RESET exactly to confirm this action.',
        ]);
    }
}
