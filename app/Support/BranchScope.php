<?php

namespace App\Support;

use App\Models\Staff;
use App\Models\User;

class BranchScope
{
    public static function canViewAllBranches(?User $user): bool
    {
        return $user && BmsNavigation::hasCap($user, 'allBranches');
    }

    public static function userBranch(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->branch && $user->branch !== 'all') {
            return $user->branch;
        }

        $staffBranch = Staff::query()->where('user_id', $user->id)->value('branch');
        if ($staffBranch && $staffBranch !== 'all') {
            return $staffBranch;
        }

        return null;
    }

    /** Active branch filter for queries; null means all branches (multi-branch users only). */
    public static function filter(?User $user): ?string
    {
        if (! self::canViewAllBranches($user)) {
            return self::userBranch($user);
        }

        $branch = session('bms_branch', 'all');

        return $branch === 'all' ? null : $branch;
    }

    /** Branch names to show in dashboards/reports for the current user. */
    public static function visibleBranchNames(array $allBranches, ?User $user): array
    {
        if (! self::canViewAllBranches($user)) {
            $branch = self::userBranch($user);

            return ($branch && in_array($branch, $allBranches, true)) ? [$branch] : [];
        }

        $filter = session('bms_branch', 'all');
        if ($filter !== 'all' && in_array($filter, $allBranches, true)) {
            return [$filter];
        }

        return $allBranches;
    }

    /** Default session branch after login. */
    public static function sessionDefault(?User $user): string
    {
        if (! self::canViewAllBranches($user)) {
            return self::userBranch($user) ?? 'all';
        }

        if ($user?->branch && $user->branch !== 'all') {
            return $user->branch;
        }

        return 'all';
    }

    /** Branch label for layout/header display. */
    public static function displayBranch(?User $user): string
    {
        if (! self::canViewAllBranches($user)) {
            return self::userBranch($user) ?? 'all';
        }

        return session('bms_branch', 'all');
    }
}
