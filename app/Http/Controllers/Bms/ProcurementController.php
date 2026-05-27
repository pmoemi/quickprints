<?php

namespace App\Http\Controllers\Bms;

use App\Models\ProcurementEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('procurement', 'read');
        $entries = $this->scopeBranch(ProcurementEntry::query())->orderByDesc('date')->get();

        return view('procurement.index', compact('entries'));
    }

    public function create(): View
    {
        $this->authorizeBms('procurement', 'create');

        return view('procurement.form', [
            'entry' => new ProcurementEntry(['date' => now()->toDateString(), 'status' => 'pending']),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('procurement', 'create');
        $data = $request->validate([
            'date' => 'required|date',
            'supplier' => 'nullable|string|max:120',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'branch' => 'nullable|string|max:80',
            'status' => 'nullable|string|max:40',
        ]);
        $data['id'] = $this->nextNumericId(ProcurementEntry::class);
        $data['requested_by'] = $request->user()->name;
        ProcurementEntry::query()->create($data);

        return redirect()->route('bms.procurement.index')->with('success', 'Procurement record created.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('procurement', 'delete');
        ProcurementEntry::query()->findOrFail($id)->delete();

        return redirect()->route('bms.procurement.index')->with('success', 'Record deleted.');
    }
}


