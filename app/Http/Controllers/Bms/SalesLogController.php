<?php

namespace App\Http\Controllers\Bms;

use App\Models\PrintJob;
use App\Models\SalesLog;
use App\Support\BmsPermissions;
use App\Services\JobDesignerNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesLogController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('saleslog', 'read');
        $summaries = $this->salesLogSummaries();
        $logs = $this->scopedSalesLogsQuery()
            ->with('salesRep')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('saleslog.index', compact('logs', 'summaries'));
    }

    public function create(): View
    {
        $this->authorizeBms('saleslog', 'create');

        $data = [
            'log' => new SalesLog([
                'date' => now()->toDateString(),
                'branch' => $this->defaultAssignableBranch(),
            ]),
            'branches' => $this->assignableBranchNames(),
            'designers' => $this->assignableDesigners($this->defaultAssignableBranch()),
        ];

        // If the role has the sales-client visibility capability, include clients
        // list (scoped by branch/session via scopedClientsQuery()).
        if (BmsPermissions::canViewBranchClients(auth()->user()?->role)) {
            $data['clients'] = $this->scopedClientsQuery()->orderBy('name')->get();
        }

        return view('saleslog.form', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('saleslog', 'create');

        $data = $request->validate([
            'date'         => 'required|date',
            // Allow selecting an existing client via client_id, or entering a new name
            'client_id'    => $this->scopedClientIdRules(),
            'client_name'  => 'required_without:client_id|string|max:120',
            'phone'        => 'nullable|string|max:40',
            'job_desc'     => 'required|string|max:255',
            'category'     => 'nullable|string|max:80',
            'branch'       => $this->assignableBranchRules(),
            'amount'       => 'required|numeric|min:0',
            'cash_amount'  => 'nullable|numeric|min:0',
            'mpesa_amount' => 'nullable|numeric|min:0',
            'pay_method'   => 'nullable|string|max:40',
            'pay_status'   => 'nullable|string|in:pending,partial,paid',
            'notes'        => 'nullable|string',
            'designer_id'  => $this->designerIdRules(true, $request->input('branch')),
        ]);

        [$payMethod, $payStatus, $amountPaid] = $this->resolvePayment(
            (float) $data['amount'],
            (float) ($data['cash_amount'] ?? 0),
            (float) ($data['mpesa_amount'] ?? 0),
            $data['pay_method'] ?? null,
            $data['pay_status'] ?? 'pending',
        );

        // If a client_id was provided, fill in the client fields from the record
        if (! empty($data['client_id'])) {
            $client = $this->scopedClientsQuery()->find($data['client_id']);
            if ($client) {
                $data['client_name'] = $client->name;
                $data['phone'] = $data['phone'] ?? $client->phone;
            }
        }

        $jobId = $this->nextJobId($data['branch'] ?? null);
        $data['id']           = $this->nextNumericId(SalesLog::class);
        $data['job_id']       = $jobId;
        $data['logged_by']    = $request->user()->name;
        $data['sales_rep_id'] = $this->currentStaffId();
        $data['cash_amount']  = max(0.0, (float) ($data['cash_amount'] ?? 0));
        $data['mpesa_amount'] = max(0.0, (float) ($data['mpesa_amount'] ?? 0));
        $data['pay_method']   = $payMethod;
        $data['pay_status']   = $payStatus;

        SalesLog::query()->create($data);

        $job = PrintJob::query()->create([
            'id'           => $jobId,
            'client_id'    => $data['client_id'] ?? null,
            'title'        => $data['job_desc'],
            'branch'       => $data['branch'],
            'category'     => $data['category'] ?? null,
            'stage'        => 'designing',
            'designer_id'  => $data['designer_id'],
            'sales_rep_id' => $data['sales_rep_id'],
            'amount'       => (float) $data['amount'],
            'amount_paid'  => $amountPaid,
            'paid'         => $payStatus === 'paid',
            'priority'     => 'medium',
            'notes'        => $data['notes'] ?? null,
            'history'      => [['action' => 'Job created from sales log and assigned to designer', 'by' => $request->user()->name, 'at' => now()->toIso8601String()]],
        ]);

        JobDesignerNotificationService::notifyAssigned($job, $request->user());

        return redirect()->route('bms.saleslog.index')->with('success', "Sale logged and job {$jobId} created.");
    }

    public function editPayment(int $id): View
    {
        $this->authorizeBms('saleslog', 'create');
        $log = $this->findScopedSalesLog($id);

        return view('saleslog.edit-payment', compact('log'));
    }

    public function updatePayment(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('saleslog', 'create');
        $log = $this->findScopedSalesLog($id);

        $data = $request->validate([
            'cash_amount'  => 'nullable|numeric|min:0',
            'mpesa_amount' => 'nullable|numeric|min:0',
            'pay_method'   => 'nullable|string|max:40',
            'pay_status'   => 'nullable|string|in:pending,partial,paid',
            'notes'        => 'nullable|string',
        ]);

        [$payMethod, $payStatus, $amountPaid] = $this->resolvePayment(
            (float) $log->amount,
            (float) ($data['cash_amount'] ?? 0),
            (float) ($data['mpesa_amount'] ?? 0),
            $data['pay_method'] ?? $log->pay_method,
            $data['pay_status'] ?? $log->pay_status,
        );

        $log->update([
            'cash_amount'  => max(0.0, (float) ($data['cash_amount'] ?? 0)),
            'mpesa_amount' => max(0.0, (float) ($data['mpesa_amount'] ?? 0)),
            'pay_method'   => $payMethod,
            'pay_status'   => $payStatus,
            'notes'        => $data['notes'] ?? $log->notes,
        ]);

        if ($log->job_id) {
            DB::table('print_jobs')->where('id', $log->job_id)->update([
                'amount_paid' => $amountPaid,
                'paid'        => $payStatus === 'paid',
            ]);
        }

        return redirect()->route('bms.saleslog.index')
            ->with('success', "Payment updated for {$log->client_name}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('saleslog', 'delete');
        $this->findScopedSalesLog($id)->delete();

        return redirect()->route('bms.saleslog.index')->with('success', 'Sale entry deleted.');
    }

    /** @return array{string|null, string, float} [pay_method, pay_status, amount_paid_for_job] */
    private function resolvePayment(
        float $amount,
        float $cash,
        float $mpesa,
        ?string $fallbackMethod,
        string $fallbackStatus,
    ): array {
        $cash  = max(0.0, $cash);
        $mpesa = max(0.0, $mpesa);
        $total = $cash + $mpesa;

        if ($total > 0) {
            $method     = $cash > 0 && $mpesa > 0 ? 'Mixed' : ($cash > 0 ? 'Cash' : 'Mpesa');
            $status     = $total >= $amount ? 'paid' : 'partial';
            $amountPaid = min($total, $amount);
        } else {
            $method     = $fallbackMethod;
            $status     = $fallbackStatus;
            $amountPaid = $status === 'paid' ? $amount : 0.0;
        }

        return [$method, $status, $amountPaid];
    }
}
