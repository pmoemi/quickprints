<?php

namespace App\Http\Controllers\Bms;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('clients', 'read');
        $clients = $this->scopedClientsQuery()->orderBy('name')->get();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorizeBms('clients', 'create');

        return view('clients.form', [
            'client' => new Client(['branch' => $this->defaultAssignableBranch()]),
            'branches' => $this->assignableBranchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('clients', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(Client::class);
        Client::query()->create($data);

        return redirect()->route('bms.clients.index')->with('success', 'Client created.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('clients', 'update');

        return view('clients.form', [
            'client' => $this->findScopedClient($id),
            'branches' => $this->assignableBranchNames(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('clients', 'update');
        $this->findScopedClient($id)->update($this->validated($request));

        return redirect()->route('bms.clients.index')->with('success', 'Client updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('clients', 'delete');
        $this->findScopedClient($id)->delete();

        return redirect()->route('bms.clients.index')->with('success', 'Client deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'company' => 'nullable|string|max:120',
            'branch' => $this->assignableBranchRules(),
            'notes' => 'nullable|string',
        ]);

        if (empty($data['branch'])) {
            $data['branch'] = $this->defaultAssignableBranch();
        }

        return $data;
    }
}
