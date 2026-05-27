<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PortalToken;
use App\Models\PrintJob;
use Illuminate\View\View;

class PortalPublicController extends Controller
{
    public function show(string $token): View
    {
        $portal = PortalToken::query()->where('token', $token)->firstOrFail();
        if ($portal->expires_at && $portal->expires_at->isPast()) {
            abort(410, 'This portal link has expired.');
        }

        $client = Client::query()->findOrFail($portal->client_id);
        $jobs = PrintJob::query()
            ->where('client_id', $client->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('portal.public', compact('client', 'jobs', 'portal'));
    }
}


