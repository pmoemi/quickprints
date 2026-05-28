<?php

namespace App\Http\Controllers\Bms;

use App\Models\Lead;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('leads', 'read');
        $leads = $this->scopeBranch(Lead::query())->orderByDesc('id')->get();

        return view('leads.index', compact('leads'));
    }

    public function create(): View
    {
        $this->authorizeBms('leads', 'create');

        return view('leads.form', [
            'lead' => new Lead(['status' => 'new']),
            'branches' => $this->branchNames(),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('leads', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(Lead::class);
        Lead::query()->create($data);

        return redirect()->route('bms.leads.index')->with('success', 'Lead created.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('leads', 'update');

        return view('leads.form', [
            'lead' => $this->findScopedLead($id),
            'branches' => $this->branchNames(),
            'staff' => Staff::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('leads', 'update');
        $this->findScopedLead($id)->update($this->validated($request));

        return redirect()->route('bms.leads.index')->with('success', 'Lead updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('leads', 'delete');
        $this->findScopedLead($id)->delete();

        return redirect()->route('bms.leads.index')->with('success', 'Lead deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_name' => 'required|string|max:120',
            'phone' => 'nullable|string|max:40',
            'service' => 'nullable|string|max:80',
            'value' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:40',
            'assigned_to' => 'nullable|string|max:80',
            'branch' => 'nullable|string|max:80',
            'follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }
}


