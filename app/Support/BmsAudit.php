<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BmsAudit
{
    public static function log(string $action): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        self::logAs($user, $action);
    }

    public static function logAs(User $user, string $action): void
    {
        AuditLog::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'branch' => $user->branch,
            'action' => $action,
        ]);
    }
}
