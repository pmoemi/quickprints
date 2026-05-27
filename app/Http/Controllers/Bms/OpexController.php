<?php

namespace App\Http\Controllers\Bms;

use App\Models\OpexEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpexController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('opex', 'read');
        $items = $this->scopeBranch(OpexEntry::query())->orderByDesc('date')->get();

        return view('opex.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('opex', 'create');

        return view('opex.form', [
            'entry' => new OpexEntry(['date' => now()->toDateString()]),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('opex', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(OpexEntry::class);
        $data['paid_by'] = $request->user()->name;
        OpexEntry::query()->create($data);

        return redirect()->route('bms.opex.index')->with('success', 'Expense recorded.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('opex', 'delete');
        OpexEntry::query()->findOrFail($id)->delete();

        return redirect()->route('bms.opex.index')->with('success', 'Expense deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'branch' => 'nullable|string|max:80',
            'pay_method' => 'nullable|string|max:40',
        ]);
    }
}


