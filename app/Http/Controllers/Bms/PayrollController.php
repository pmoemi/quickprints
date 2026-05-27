<?php

namespace App\Http\Controllers\Bms;

use App\Models\PayrollEntry;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('payroll', 'read');
        $entries = PayrollEntry::query()->orderByDesc('month')->get();

        return view('payroll.index', compact('entries'));
    }

    public function create(): View
    {
        $this->authorizeBms('payroll', 'create');

        return view('payroll.form', [
            'entry' => new PayrollEntry(['month' => now()->format('Y-m'), 'status' => 'pending']),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('payroll', 'create');
        $data = $request->validate([
            'month' => 'required|string|max:20',
            'staff_id' => 'required|integer',
            'staff_name' => 'nullable|string',
            'gross_salary' => 'required|numeric|min:0',
            'nhif' => 'nullable|numeric',
            'nssf' => 'nullable|numeric',
            'paye' => 'nullable|numeric',
            'net_pay' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);
        $data['id'] = $this->nextNumericId(PayrollEntry::class);
        if (empty($data['staff_name'])) {
            $data['staff_name'] = Staff::query()->find($data['staff_id'])?->name;
        }
        PayrollEntry::query()->create($data);

        return redirect()->route('bms.payroll.index')->with('success', 'Payroll entry added.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('payroll', 'delete');
        PayrollEntry::query()->findOrFail($id)->delete();

        return redirect()->route('bms.payroll.index')->with('success', 'Entry deleted.');
    }
}


