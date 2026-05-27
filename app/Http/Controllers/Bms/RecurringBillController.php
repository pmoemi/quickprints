<?php

namespace App\Http\Controllers\Bms;

use App\Models\RecurringBill;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringBillController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('recurring_bills', 'read');
        $items = $this->scopeBranch(RecurringBill::query())->orderBy('name')->get();

        return view('recurring-bills.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('recurring_bills', 'create');

        return view('recurring-bills.form', [
            'item' => new RecurringBill(['due_day' => 1]),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('recurring_bills', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(RecurringBill::class);
        RecurringBill::query()->create($data);
        BmsAudit::log('Added recurring bill: '.$data['name']);

        return redirect()->route('bms.recurring-bills.index')->with('success', 'Recurring bill added.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('recurring_bills', 'update');
        $item = RecurringBill::query()->findOrFail($id);
        if ($request->boolean('mark_paid')) {
            $item->update(['last_paid_at' => now()->toDateString()]);
            BmsAudit::log('Marked recurring bill paid: '.$item->name);

            return redirect()->route('bms.recurring-bills.index')->with('success', 'Marked as paid.');
        }
        $item->update($this->validated($request));
        BmsAudit::log('Updated recurring bill: '.$item->name);

        return redirect()->route('bms.recurring-bills.index')->with('success', 'Recurring bill updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('recurring_bills', 'delete');
        $item = RecurringBill::query()->findOrFail($id);
        $name = $item->name;
        $item->delete();
        BmsAudit::log('Deleted recurring bill: '.$name);

        return redirect()->route('bms.recurring-bills.index')->with('success', 'Recurring bill deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'branch' => 'nullable|string|max:80',
            'amount' => 'required|numeric|min:0',
            'due_day' => 'required|integer|min:1|max:31',
            'vendor' => 'nullable|string|max:120',
        ]);
    }
}


