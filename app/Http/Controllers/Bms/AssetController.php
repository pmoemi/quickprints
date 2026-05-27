<?php

namespace App\Http\Controllers\Bms;

use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('assets', 'read');
        $assets = $this->scopeBranch(Asset::query())->orderBy('name')->get();

        return view('assets.index', compact('assets'));
    }

    public function create(): View
    {
        $this->authorizeBms('assets', 'create');

        return view('assets.form', [
            'asset' => new Asset(['condition_status' => 'Good']),
            'branches' => $this->branchNames(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('assets', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(Asset::class);
        Asset::query()->create($data);

        return redirect()->route('bms.assets.index')->with('success', 'Asset added.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('assets', 'update');

        return view('assets.form', [
            'asset' => Asset::query()->findOrFail($id),
            'branches' => $this->branchNames(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('assets', 'update');
        Asset::query()->findOrFail($id)->update($this->validated($request));

        return redirect()->route('bms.assets.index')->with('success', 'Asset updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('assets', 'delete');
        Asset::query()->findOrFail($id)->delete();

        return redirect()->route('bms.assets.index')->with('success', 'Asset deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'condition_status' => 'nullable|string|max:40',
            'branch' => 'nullable|string|max:80',
        ]);
    }
}


