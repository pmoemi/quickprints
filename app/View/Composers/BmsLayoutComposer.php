<?php

namespace App\View\Composers;

use App\Services\BmsSettingsService;
use App\Support\BmsNavigation;
use App\Support\BmsPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BmsLayoutComposer
{
    public function __construct(
        protected BmsSettingsService $settings
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $settings = $this->settings->all();
        $role = $user?->role ?? 'Guest';

        // Per-user theme overrides the system default
        $effectiveTheme = $user?->theme ?? $settings['theme'] ?? 'dark';
        $settings['theme'] = $effectiveTheme;

        $view->with([
            'bmsUser' => $user,
            'bmsSettings' => $settings,
            'bmsNav' => BmsNavigation::navForUser($user),
            'bmsBranches' => $settings['branches'] ?? [],
            'bmsBranch' => session('bms_branch', $user?->branch === 'all' ? 'all' : ($user?->branch ?? 'all')),
            'bmsCanAllBranches' => BmsNavigation::hasCap($user, 'allBranches'),
            'bmsPermissions' => BmsPermissions::forRole($role),
        ]);
    }
}
