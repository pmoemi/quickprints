<?php

namespace App\Http\Controllers\Bms;

use App\Models\PettyCashEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PettyCashController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('pettycash', 'read');
        $entries = $this->scopeBranch(PettyCashEntry::query())->orderByDesc('date')->get();

        return view('pettycash.index', compact('entries'));
    }

    public function create(): View
    {
        $this->authorizeBms('pettycash', 'create');

        return view('pettycash.form', [
            'entry' => new PettyCashEntry(['date' => now()->toDateString(), 'status' => 'pending']),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('pettycash', 'create');
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'branch' => 'nullable|string|max:80',
            'status' => 'nullable|string|max:40',
        ]);
        $data['id'] = $this->nextNumericId(PettyCashEntry::class);
        $data['submitted_by'] = $request->user()->name;
        PettyCashEntry::query()->create($data);

        return redirect()->route('bms.pettycash.index')->with('success', 'Petty cash request submitted.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('pettycash', 'delete');
        PettyCashEntry::query()->findOrFail($id)->delete();

        return redirect()->route('bms.pettycash.index')->with('success', 'Entry deleted.');
    }
}


