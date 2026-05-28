<?php

namespace App\Http\Controllers\Bms;

use App\Models\PrintJob;
use App\Models\SalesLog;
use App\Services\JobDesignerNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesLogController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('saleslog', 'read');
        $logs = $this->scopeBranch(SalesLog::query())->orderByDesc('date')->orderByDesc('id')->get();

        return view('saleslog.index', compact('logs'));
    }

    public function create(): View
    {
        $this->authorizeBms('saleslog', 'create');

        return view('saleslog.form', [
            'log' => new SalesLog([
                'date' => now()->toDateString(),
                'branch' => $this->defaultAssignableBranch(),
            ]),
            'branches' => $this->assignableBranchNames(),
            'designers' => $this->assignableDesigners($this->defaultAssignableBranch()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('saleslog', 'create');

        $data = $request->validate([
            'date' => 'required|date',
            'client_name' => 'required|string|max:120',
            'phone' => 'nullable|string|max:40',
            'job_desc' => 'required|string|max:255',
            'category' => 'nullable|string|max:80',
            'branch' => $this->assignableBranchRules(),
            'amount' => 'required|numeric|min:0',
            'pay_method' => 'nullable|string|max:40',
            'pay_status' => 'nullable|string|in:pending,partial,paid',
            'notes' => 'nullable|string',
            'designer_id' => $this->designerIdRules(true, $request->input('branch')),
        ]);

        $jobId = $this->nextJobId($data['branch'] ?? null);
        $data['id'] = $this->nextNumericId(SalesLog::class);
        $data['job_id'] = $jobId;
        $data['logged_by'] = $request->user()->name;
        $payStatus = $data['pay_status'] ?? 'pending';
        $data['pay_status'] = $payStatus;

        if ($payStatus === 'partial') {
            $request->validate(['amount_paid' => 'required|numeric|min:0.01']);
        }

        SalesLog::query()->create($data);

        $amount = (float) $data['amount'];
        $amountPaid = match ($payStatus) {
            'paid' => $amount,
            'partial' => min((float) ($request->input('amount_paid') ?? 0), $amount),
            default => 0.0,
        };

        $job = PrintJob::query()->create([
            'id' => $jobId,
            'client_id' => null,
            'title' => $data['job_desc'],
            'branch' => $data['branch'],
            'category' => $data['category'] ?? null,
            'stage' => 'designing',
            'designer_id' => $data['designer_id'],
            'amount' => $amount,
            'amount_paid' => $amountPaid,
            'priority' => 'medium',
            'notes' => $data['notes'] ?? null,
            'history' => [['action' => 'Job created from sales log and assigned to designer', 'by' => $request->user()->name, 'at' => now()->toIso8601String()]],
        ]);

        JobDesignerNotificationService::notifyAssigned($job, $request->user());

        return redirect()->route('bms.saleslog.index')->with('success', "Sale logged and job {$jobId} created.");
    }

    public function destroy(int $id): RedirectResponse // route param: id
    {
        $this->authorizeBms('saleslog', 'delete');
        $this->findScopedSalesLog($id)->delete();

        return redirect()->route('bms.saleslog.index')->with('success', 'Sale entry deleted.');
    }
}


