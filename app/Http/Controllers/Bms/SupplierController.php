<?php

namespace App\Http\Controllers\Bms;

use App\Models\Supplier;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('suppliers', 'read');
        $items = Supplier::query()->orderBy('name')->get();

        return view('suppliers.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('suppliers', 'create');

        return view('suppliers.form', ['item' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('suppliers', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(Supplier::class);
        Supplier::query()->create($data);
        BmsAudit::log('Added supplier: '.$data['name']);

        return redirect()->route('bms.suppliers.index')->with('success', 'Supplier added.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('suppliers', 'update');

        return view('suppliers.form', ['item' => Supplier::query()->findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('suppliers', 'update');
        $item = Supplier::query()->findOrFail($id);
        $item->update($this->validated($request));
        BmsAudit::log('Updated supplier: '.$item->name);

        return redirect()->route('bms.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('suppliers', 'delete');
        $item = Supplier::query()->findOrFail($id);
        $name = $item->name;
        $item->delete();
        BmsAudit::log('Deleted supplier: '.$name);

        return redirect()->route('bms.suppliers.index')->with('success', 'Supplier deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'contact' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'category' => 'nullable|string|max:80',
            'payment_terms' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
        ]);
    }
}


