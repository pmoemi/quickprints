<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Impersonation
{
    public const SESSION_KEY = 'impersonator_id';

    public const BRANCH_KEY = 'impersonator_branch';

    public static function isActive(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    public static function impersonator(): ?User
    {
        $id = session(self::SESSION_KEY);

        return $id ? User::query()->find($id) : null;
    }

    public static function start(User $admin, User $target): void
    {
        session([
            self::SESSION_KEY => $admin->id,
            self::BRANCH_KEY => session('bms_branch'),
        ]);

        Auth::login($target);
        session(['bms_branch' => BranchScope::sessionDefault($target)]);
    }

    public static function leave(): ?User
    {
        $adminId = session(self::SESSION_KEY);
        if (! $adminId) {
            return null;
        }

        $admin = User::query()->find($adminId);
        Auth::loginUsingId($adminId);
        session(['bms_branch' => session(self::BRANCH_KEY, BranchScope::sessionDefault($admin))]);
        session()->forget([self::SESSION_KEY, self::BRANCH_KEY]);

        return $admin;
    }
}
