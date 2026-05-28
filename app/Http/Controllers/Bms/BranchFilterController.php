<?php

namespace App\Http\Controllers\Bms;

use App\Support\BranchScope;
use App\Support\BmsNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchFilterController extends BmsController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! BmsNavigation::hasCap($user, 'allBranches')) {
            session(['bms_branch' => BranchScope::userBranch($user) ?? 'all']);
        } else {
            $branch = $request->input('branch', 'all');
            session(['bms_branch' => $branch]);
        }

        return back();
    }
}

