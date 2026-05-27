<?php

namespace App\Http\Controllers\Bms;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchFilterController extends BmsController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! in_array($user->role, ['Admin', 'General Manager'], true)) {
            session(['bms_branch' => $user->branch ?? 'all']);
        } else {
            $branch = $request->input('branch', 'all');
            session(['bms_branch' => $branch]);
        }

        return back();
    }
}

