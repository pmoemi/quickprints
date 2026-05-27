<?php

namespace App\Http\Controllers\Bms;

use App\Models\LedgerEntry;
use App\Support\BmsAudit;
use App\Support\LedgerAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LedgerController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('ledger', 'read');
        $entries = LedgerEntry::query()->orderByDesc('date')->orderByDesc('id')->get();
        $accounts = LedgerAccounts::chart();

        return view('ledger.index', compact('entries', 'accounts'));
    }

    public function create(): View
    {
        $this->authorizeBms('ledger', 'create');

        return view('ledger.form', [
            'accounts' => LedgerAccounts::chart(),
            'entry' => new LedgerEntry(['date' => now()->toDateString()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('ledger', 'create');
        $data = $request->validate([
            'date' => 'required|date',
            'ref' => 'nullable|string|max:80',
            'description' => 'required|string|max:255',
            'debit_account' => 'required|string',
            'debit_amount' => 'required|numeric|min:0.01',
            'credit_account' => 'required|string',
            'credit_amount' => 'required|numeric|min:0.01',
        ]);

        $lines = [
            ['account' => $data['debit_account'], 'debit' => (float) $data['debit_amount'], 'credit' => 0],
            ['account' => $data['credit_account'], 'debit' => 0, 'credit' => (float) $data['credit_amount']],
        ];

        $id = $this->nextNumericId(LedgerEntry::class);
        LedgerEntry::query()->create([
            'id' => $id,
            'entry_id' => 'JE-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT),
            'date' => $data['date'],
            'ref' => $data['ref'] ?? 'MANUAL',
            'description' => $data['description'],
            'posted_by' => Auth::user()->name,
            'lines' => $lines,
        ]);
        BmsAudit::log('Posted journal entry: '.$data['description']);

        return redirect()->route('bms.ledger.index')->with('success', 'Journal entry posted.');
    }
}


