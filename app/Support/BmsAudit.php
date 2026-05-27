<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class BmsAudit
{
    public static function log(string $action): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'branch' => $user->branch,
            'action' => $action,
        ]);
    }
}
