<?php

namespace App\Http\Controllers\Bms;

use App\Models\ServiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicesController extends BmsController
{
    public function index(): View
    {
        $items = ServiceItem::query()->orderBy('category')->orderBy('sort_order')->orderBy('name')->get();
        $catalog = $items->groupBy('category');

        return view('services.index', compact('catalog', 'items'));
    }

    public function create(): View
    {
        $this->authorizeBms('services', 'create');
        $categories = ServiceItem::query()->distinct()->orderBy('category')->pluck('category');

        return view('services.form', [
            'item'       => new ServiceItem(),
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('services', 'create');
        $data = $this->validated($request);
        $data['id'] = $this->nextNumericId(ServiceItem::class);
        ServiceItem::query()->create($data);

        return redirect()->route('bms.services.index')->with('success', 'Service added.');
    }

    public function edit(int $id): View
    {
        $this->authorizeBms('services', 'update');
        $categories = ServiceItem::query()->distinct()->orderBy('category')->pluck('category');

        return view('services.form', [
            'item'       => ServiceItem::query()->findOrFail($id),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeBms('services', 'update');
        ServiceItem::query()->findOrFail($id)->update($this->validated($request));

        return redirect()->route('bms.services.index')->with('success', 'Service updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('services', 'delete');
        ServiceItem::query()->findOrFail($id)->delete();

        return redirect()->route('bms.services.index')->with('success', 'Service deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'category'   => 'required|string|max:100',
            'name'       => 'required|string|max:150',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
