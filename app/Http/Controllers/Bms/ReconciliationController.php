<?php

namespace App\Http\Controllers\Bms;

use App\Models\BankStatementLine;
use App\Models\OpexEntry;
use App\Models\PettyCashEntry;
use App\Models\PrintJob;
use App\Models\SalesLog;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReconciliationController extends BmsController
{
    public function bank(): View
    {
        $this->authorizeBms('ledger', 'read');
        $lines = BankStatementLine::query()->orderByDesc('date')->get();
        $unmatched = $lines->where('matched', false)->count();

        return view('reconciliation.bank', compact('lines', 'unmatched'));
    }

    public function storeBankLine(Request $request): RedirectResponse
    {
        $this->authorizeBms('ledger', 'create');
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0',
            'bank' => 'nullable|string|max:80',
        ]);
        $data['id'] = $this->nextNumericId(BankStatementLine::class);
        BankStatementLine::query()->create($data);
        BmsAudit::log('Added bank statement line');

        return redirect()->route('bms.bank-recon')->with('success', 'Statement line added.');
    }

    public function matchBankLine(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('ledger', 'update');
        $ref = $request->validate(['matched_ref' => 'required|string|max:120'])['matched_ref'];
        BankStatementLine::query()->findOrFail($id)->update(['matched' => true, 'matched_ref' => $ref]);
        BmsAudit::log('Matched bank line #'.$id);

        return redirect()->route('bms.bank-recon')->with('success', 'Line matched.');
    }

    public function cash(): View
    {
        $this->authorizeBms('ledger', 'read');
        $branch = $this->branchFilter();
        $salesQuery = SalesLog::query();
        $jobsQuery = PrintJob::query()->where('paid', true);
        $pettyQuery = PettyCashEntry::query();
        $opexQuery = OpexEntry::query();

        if ($branch) {
            $salesQuery->where('branch', $branch);
            $jobsQuery->where('branch', $branch);
            $pettyQuery->where('branch', $branch);
            $opexQuery->where('branch', $branch);
        }

        $cashIn = (float) $salesQuery->sum('amount') + (float) $jobsQuery->sum('amount');
        $cashOut = (float) $pettyQuery->where('type', 'out')->sum('amount')
            + (float) $opexQuery->sum('amount');
        $pettyBalance = (float) $pettyQuery->sum('amount');

        return view('reconciliation.cash', compact('cashIn', 'cashOut', 'pettyBalance', 'branch'));
    }
}


