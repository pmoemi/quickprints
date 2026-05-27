<?php

namespace App\Http\Controllers\Bms;

use App\Models\Client;
use App\Models\PortalToken;
use App\Models\PrintJob;
use App\Support\BmsAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('clients', 'read');
        $tokens = PortalToken::query()->with('client')->orderByDesc('created_at')->get();
        $clients = Client::query()->orderBy('name')->get();

        return view('portal.index', compact('tokens', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBms('clients', 'update');
        $data = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'access_level' => 'nullable|string|max:40',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        PortalToken::query()->create([
            'client_id' => $data['client_id'],
            'token' => Str::random(48),
            'access_level' => $data['access_level'] ?? 'basic',
            'expires_at' => isset($data['days']) ? now()->addDays((int) $data['days']) : null,
        ]);
        BmsAudit::log('Generated client portal link');

        return redirect()->route('bms.portal.index')->with('success', 'Portal link created.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeBms('clients', 'delete');
        PortalToken::query()->findOrFail($id)->delete();
        BmsAudit::log('Revoked portal link');

        return redirect()->route('bms.portal.index')->with('success', 'Link revoked.');
    }
}


