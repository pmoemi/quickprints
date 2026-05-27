<?php

namespace App\View\Composers;

use App\Models\ServiceItem;
use App\Services\BmsSettingsService;
use App\Support\BmsNavigation;
use App\Support\BmsPermissions;
use App\Support\BrandColors;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BmsLayoutComposer
{
    public function __construct(
        protected BmsSettingsService $settings
    ) {}

    public function compose(View $view): void
    {
        $name = $view->getName();
        foreach (['auth.', 'emails.', 'pdf.', 'portal.public'] as $excluded) {
            if (str_starts_with($name, $excluded) || $name === rtrim($excluded, '.')) {
                return;
            }
        }

        $user = Auth::user();
        $settings = $this->settings->all();
        $role = $user?->role ?? 'Guest';

        // Per-user theme overrides the system default
        $effectiveTheme = $user?->theme ?? $settings['theme'] ?? 'dark';
        $settings['theme'] = $effectiveTheme;

        $jobCategories = [];
        try {
            $jobCategories = ServiceItem::query()->distinct()->orderBy('category')->pluck('category')->filter()->values()->toArray();
        } catch (\Throwable) {
        }

        $view->with([
            'bmsUser' => $user,
            'bmsSettings' => $settings,
            'bmsBrand' => BrandColors::fromSettings($settings),
            'bmsNav' => BmsNavigation::navForUser($user),
            'bmsBranches' => $settings['branches'] ?? [],
            'bmsBranch' => session('bms_branch', $user?->branch === 'all' ? 'all' : ($user?->branch ?? 'all')),
            'bmsCanAllBranches' => BmsNavigation::hasCap($user, 'allBranches'),
            'bmsPermissions' => BmsPermissions::forRole($role),
            'bmsCurrency' => $settings['currency_symbol'] ?? 'KSh',
            'bmsPaymentMethods' => $settings['payment_methods'] ?? ['Mpesa', 'Cash', 'Bank Transfer', 'Cheque', 'Credit'],
            'bmsJobCategories' => $jobCategories,
        ]);
    }
}
