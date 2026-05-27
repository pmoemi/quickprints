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

    /**
     * Convert a branch name to a short uppercase code.
     * Multi-word → first letter of each word ("Ngong Road" → "NR").
     * Single word → first 3 letters ("Westlands" → "WES", "CBD" → "CBD").
     */
    protected static function branchCode(string $branch): string
    {
        $branch = trim($branch);
        $words = preg_split('/[\s\-_\/]+/', $branch, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 1) {
            return strtoupper(implode('', array_map(fn($w) => $w[0], $words)));
        }

        return strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $branch), 0, 3));
    }

    protected function nextJobId(?string $branchName = null): string
    {
        $nb     = $this->settings->numbering('_all') ?? [];
        $base   = $nb['job_prefix'] ?? 'QP';
        $pad    = (int) ($nb['job_pad'] ?? 5);
        $start  = (int) ($nb['job_start'] ?? 10001);

        // Always include branch code when a branch is given
        $prefix = ($branchName && $branchName !== 'all')
            ? $base.'-'.self::branchCode($branchName)
            : $base;

        // Per-prefix counter — look for the last job matching this exact prefix
        $last = \App\Models\PrintJob::query()
            ->where('id', 'like', $prefix.'-%')
            ->orderByDesc('id')
            ->value('id');

        $num = $start;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return $prefix.'-'.str_pad((string) $num, $pad, '0', STR_PAD_LEFT);
    }

    /** Format a quote ID for display: e.g. QT-0003 */
    protected function formatQuoteRef(int $id): string
    {
        $prefix = $this->settings->numbering('quote_prefix');
        $pad    = (int) $this->settings->numbering('quote_pad');

        return $prefix.'-'.str_pad((string) $id, $pad, '0', STR_PAD_LEFT);
    }

    /** Format a job ID as an invoice reference for display: e.g. INV-QP-10001 */
    public static function invoiceRef(string $jobId, array $settings): string
    {
        $prefix = $settings['numbering']['invoice_prefix'] ?? 'INV';

        return $prefix.'-'.$jobId;
    }

    /** Format a job ID as a receipt reference for display: e.g. RCP-QP-10001 */
    public static function receiptRef(string $jobId, array $settings): string
    {
        $prefix = $settings['numbering']['receipt_prefix'] ?? 'RCP';

        return $prefix.'-'.$jobId;
    }

    protected function nextNumericId(string $modelClass): int
    {
        $max = $modelClass::query()->max('id');

        return ($max ? (int) $max : 0) + 1;
    }
}

