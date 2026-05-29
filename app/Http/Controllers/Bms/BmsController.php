<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use App\Services\BmsSettingsService;
use App\Support\BmsPermissions;
use App\Support\BmsNavigation;
use App\Support\BmsSettingsDefaults;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\PrintJob;
use App\Models\Quote;
use App\Models\SalesLog;
use App\Models\Staff;
use App\Support\BranchScope;
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

    protected function canViewAllBranches(): bool
    {
        return BranchScope::canViewAllBranches(Auth::user());
    }

    protected function userBranch(): ?string
    {
        return BranchScope::userBranch(Auth::user());
    }

    protected function branchFilter(): ?string
    {
        return BranchScope::filter(Auth::user());
    }

    protected function visibleBranchNames(): array
    {
        return BranchScope::visibleBranchNames($this->branchNames(), Auth::user());
    }

    protected function scopeBranch(Builder $query, string $column = 'branch'): Builder
    {
        if (! $this->canViewAllBranches()) {
            $branch = $this->userBranch();
            if (! $branch) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where($column, $branch);
        }

        $branch = session('bms_branch', 'all');
        if ($branch !== 'all') {
            $query->where($column, $branch);
        }

        return $query;
    }

    protected function scopedJobsQuery(): Builder
    {
        return $this->scopeBranch(PrintJob::query());
    }

    protected function findScopedJob(string $id): PrintJob
    {
        return $this->scopedJobsQuery()->findOrFail($id);
    }

    protected function scopedSalesLogsQuery(): Builder
    {
        return $this->scopeBranch(SalesLog::query());
    }

    protected function findScopedSalesLog(int $id): SalesLog
    {
        return $this->scopedSalesLogsQuery()->findOrFail($id);
    }

    protected function scopedLeadsQuery(): Builder
    {
        return $this->scopeBranch(Lead::query());
    }

    protected function findScopedLead(int $id): Lead
    {
        return $this->scopedLeadsQuery()->findOrFail($id);
    }

    protected function scopedQuotesQuery(): Builder
    {
        return $this->scopeBranch(Quote::query());
    }

    protected function findScopedQuote(int $id): Quote
    {
        return $this->scopedQuotesQuery()->findOrFail($id);
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

    /** @return list<mixed> */
    protected function requiredScopedClientIdRules(): array
    {
        return [
            'required',
            'integer',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->scopedClientsQuery()->where('id', $value)->exists()) {
                    $fail('The selected client is not available for your branch.');
                }
            },
        ];
    }

    protected function createInlineClient(Request $request, string $branch): Client
    {
        $data = $request->validate([
            'new_client_name' => 'required|string|max:120',
            'new_client_phone' => 'nullable|string|max:40',
            'new_client_email' => 'nullable|email|max:120',
            'new_client_company' => 'nullable|string|max:120',
        ]);

        return Client::query()->create([
            'id' => $this->nextNumericId(Client::class),
            'name' => $data['new_client_name'],
            'phone' => $data['new_client_phone'] ?? null,
            'email' => $data['new_client_email'] ?? null,
            'company' => $data['new_client_company'] ?? null,
            'branch' => $branch,
        ]);
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
            $branch = $this->userBranch();
            if ($branch && in_array($branch, $all, true)) {
                return [$branch];
            }

            return [];
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
            ->where('is_designer', true)
            ->orderBy('name');

        $scopeBranch = $branch ?? $this->branchFilter();
        if ($scopeBranch) {
            $query->where(function ($q) use ($scopeBranch) {
                $q->where('branch', $scopeBranch)->orWhere('branch', 'all');
            });
        }

        return $query->get();
    }

    protected function currentStaffId(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return Staff::query()->where('user_id', $user->id)->value('id');
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

