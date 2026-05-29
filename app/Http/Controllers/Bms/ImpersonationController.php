<?php

namespace App\Http\Controllers\Bms;

use App\Models\User;
use App\Support\BmsAudit;
use App\Support\BmsNavigation;
use App\Support\Impersonation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends BmsController
{
    public function start(Request $request, int $userId): RedirectResponse
    {
        if (Auth::user()?->role !== 'Admin') {
            abort(403, 'Only administrators can impersonate staff accounts.');
        }

        if (Impersonation::isActive()) {
            return redirect()->back()->with('error', 'Already viewing as another user. Return to your admin account first.');
        }

        $target = User::query()->findOrFail($userId);

        if ($target->role === 'Admin' || $target->id === Auth::id()) {
            abort(403, 'You cannot impersonate this account.');
        }

        $admin = Auth::user();
        BmsAudit::logAs($admin, 'Started impersonating '.$target->name.' ('.$target->role.')');

        Impersonation::start($admin, $target);
        $request->session()->regenerate();

        return redirect()
            ->route(BmsNavigation::defaultRoute($target))
            ->with('success', "Now viewing as {$target->name}.");
    }

    public function leave(Request $request): RedirectResponse
    {
        if (! Impersonation::isActive()) {
            return redirect()->route('bms.dashboard');
        }

        $impersonated = Auth::user();
        $admin = Impersonation::leave();
        $request->session()->regenerate();

        if ($admin && $impersonated) {
            BmsAudit::logAs($admin, 'Stopped impersonating '.$impersonated->name);
        }

        return redirect()
            ->route('bms.staff.index')
            ->with('success', 'Returned to your admin account.');
    }
}
