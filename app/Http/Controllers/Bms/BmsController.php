<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Services\BmsSettingsService;
use App\Support\BmsPermissions;
use App\Support\BmsNavigation;
use App\Support\BmsSettingsDefaults;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

    protected function scopedClientsQuery(): Builder
    {
        return $this->scopeBranch(Client::query());
    }

    /** @return Collection<int|string, Client> */
    protected function scopedClientsKeyBy(): Collection
    {
        return $this->scopedClientsQuery()->orderBy('name')->get()->keyBy('id');
    }

    protected function scopedInventoryQuery(): Builder
    {
        return $this->scopeBranch(InventoryItem::query());
    }

    protected function findScopedClient(int $id): Client
    {
        return $this->scopedClientsQuery()->findOrFail($id);
    }

    protected function findScopedInventoryItem(int $id): InventoryItem
    {
        return $this->scopedInventoryQuery()->findOrFail($id);
    }

    protected function canAssignGlobalInventory(): bool
    {
        $user = Auth::user();
        if (! $user || ! BmsNavigation::hasCap($user, 'allBranches')) {
            return false;
        }

        return session('bms_branch', 'all') === 'all';
    }

    /** @return list<string> */
    protected function assignableInventoryBranches(): array
    {
        if ($this->canAssignGlobalInventory()) {
            return array_merge(['all'], $this->assignableBranchNames());
        }

        return $this->assignableBranchNames();
    }

    /** @return list<string> */
    protected function assignableInventoryBranchRules(): array
    {
        return ['required', 'string', 'max:80', Rule::in($this->assignableInventoryBranches())];
    }

    /** @return list<mixed> */
    protected function scopedClientIdRules(): array
    {
        return [
            'nullable',
            'integer',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                if (! $this->scopedClientsQuery()->where('id', $value)->exists()) {
                    $fail('The selected client is not available for your branch.');
                }
            },
        ];
    }

    protected function isSafeInternalUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        return $appHost && $urlHost && strcasecmp($appHost, $urlHost) === 0;
    }

    protected function normalizeInternalUrl(string $url): string
    {
        return str_starts_with($url, '/') ? url($url) : $url;
    }

    protected function resolveBackUrl(Request $request, string $fallback, string $excludePath = '/invoice'): string
    {
        if ($return = $request->query('return')) {
            $decoded = urldecode($return);
            if ($this->isSafeInternalUrl($decoded) && ! str_contains($decoded, $excludePath)) {
                return $this->normalizeInternalUrl($decoded);
            }
        }

        $previous = url()->previous();
        if ($this->isSafeInternalUrl($previous) && ! str_contains($previous, $excludePath)) {
            return $this->normalizeInternalUrl($previous);
        }

        return $fallback;
    }

    protected function branchNames(): array
    {
        return $this->bmsSettings()['branches'] ?? BmsSettingsDefaults::all()['branches'];
    }

    /** Branches the current user may assign when creating/editing records. */
    protected function assignableBranchNames(): array
    {
        $all = $this->branchNames();
        $user = Auth::user();

        if (! $user) {
            return $all;
        }

        if (! BmsNavigation::hasCap($user, 'allBranches')) {
            $branch = $user->branch;
            if ($branch && $branch !== 'all' && in_array($branch, $all, true)) {
                return [$branch];
            }

            $filtered = $this->branchFilter();
            if ($filtered && in_array($filtered, $all, true)) {
                return [$filtered];
            }

            return $all;
        }

        $pipeline = session('bms_branch', 'all');
        if ($pipeline !== 'all' && in_array($pipeline, $all, true)) {
            return [$pipeline];
        }

        return $all;
    }

    protected function defaultAssignableBranch(): ?string
    {
        return $this->assignableBranchNames()[0] ?? null;
    }

    /** @return list<string> */
    protected function assignableBranchRules(): array
    {
        return ['required', 'string', 'max:80', Rule::in($this->assignableBranchNames())];
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

    /** @return \Illuminate\Database\Eloquent\Collection<int, Staff> */
    protected function assignableDesigners(?string $branch = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Staff::query()
            ->where('active', true)
            ->where('role', 'Designer')
            ->orderBy('name');

        $scopeBranch = $branch ?? $this->branchFilter();
        if ($scopeBranch) {
            $query->where(function ($q) use ($scopeBranch) {
                $q->where('branch', $scopeBranch)->orWhere('branch', 'all');
            });
        }

        return $query->get();
    }

    /** @return list<mixed> */
    protected function designerIdRules(bool $required = false, ?string $branch = null): array
    {
        $ids = $this->assignableDesigners($branch)->pluck('id')->all();

        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::in($ids),
        ];
    }
}

