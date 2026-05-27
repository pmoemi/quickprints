<?php

namespace App\Http\Controllers\Bms;

use App\Models\PurchaseOrder;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('purchase_orders', 'read');
        $items = $this->scopeBranch(PurchaseOrder::query())->orderByDesc('date')->get();

        return view('purchase-orders.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeBms('purchase_orders', 'create');

        return view('purchase-orders.form', [
            'item' => new PurchaseOrder(['date' => now()->toDateString(), 'status' => 'pending']),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('purchase_orders', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(PurchaseOrder::class);
        $data['requested_by'] = $request->user()->name;
        PurchaseOrder::query()->create($data);
        BmsAudit::log('Created PO #'.$data['id']);

        return redirect()->route('bms.purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('purchase_orders', 'delete');
        PurchaseOrder::query()->findOrFail($id)->delete();
        BmsAudit::log('Deleted PO #'.$id);

        return redirect()->route('bms.purchase-orders.index')->with('success', 'Purchase order deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'date' => 'required|date',
            'supplier' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'branch' => 'nullable|string|max:80',
            'status' => 'nullable|string|max:40',
        ]);
    }
}


