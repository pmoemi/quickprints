<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Services\BmsSettingsService;
use App\Support\BmsPermissions;
use App\Support\BmsSettingsDefaults;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BmsController extends Controller
{
    public function __construct(
        protected BmsSettingsService $settings
    ) {}

    protected function authorizeBms(string $resource, string $action): void
    {
        $role = Auth::user()?->role;

        if (! BmsPermissions::allowed($role, $resource, $action)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /** @return array<string, mixed> */
    protected function bmsSettings(): array
    {
        return $this->settings->all();
    }

    protected function branchFilter(): ?string
    {
        $user = Auth::user();
        if (! $user || ! $user->branch || $user->branch === 'all') {
            $branch = session('bms_branch', 'all');

            return $branch === 'all' ? null : $branch;
        }

        return $user->branch;
    }

    protected function scopeBranch(Builder $query, string $column = 'branch'): Builder
    {
        $branch = $this->branchFilter();
        if ($branch) {
            $query->where($column, $branch);
        }

        return $query;
    }

    protected function branchNames(): array
    {
        return $this->bmsSettings()['branches'] ?? BmsSettingsDefaults::all()['branches'];
    }

    protected function nextJobId(): string
    {
        $last = \App\Models\PrintJob::query()->orderByDesc('id')->value('id');
        $num = 10001;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return 'QP-'.str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    protected function nextNumericId(string $modelClass): int
    {
        $max = $modelClass::query()->max('id');

        return ($max ? (int) $max : 0) + 1;
    }
}

