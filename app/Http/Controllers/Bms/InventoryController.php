<?php

namespace App\Http\Controllers\Bms;

use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('inventory', 'read');
        $items = $this->scopeBranch(InventoryItem::query())->orderBy('name')->get();

        return view('inventory.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('inventory', 'create');

        return view('inventory.form', [
            'item' => new InventoryItem(['qty' => 0, 'reorder_level' => 2]),
            'branches' => array_merge(['all'], $this->branchNames()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('inventory', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(InventoryItem::class);
        InventoryItem::query()->create($data);

        return redirect()->route('bms.inventory.index')->with('success', 'Item added.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('inventory', 'update');

        return view('inventory.form', [
            'item' => InventoryItem::query()->findOrFail($id),
            'branches' => array_merge(['all'], $this->branchNames()),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('inventory', 'update');
        InventoryItem::query()->findOrFail($id)->update($this->validated($request));

        return redirect()->route('bms.inventory.index')->with('success', 'Item updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('inventory', 'delete');
        InventoryItem::query()->findOrFail($id)->delete();

        return redirect()->route('bms.inventory.index')->with('success', 'Item deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'cat' => 'nullable|string|max:80',
            'unit' => 'nullable|string|max:20',
            'qty' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:80',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);
    }
}


