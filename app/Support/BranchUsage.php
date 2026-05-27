<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\OpexEntry;
use App\Models\PayrollEntry;
use App\Models\PettyCashEntry;
use App\Models\PrintJob;
use App\Models\ProcurementEntry;
use App\Models\Quote;
use App\Models\SalesLog;
use App\Models\Staff;
use App\Models\User;

class BranchUsage
{
    /** @return array{total: int, breakdown: array<string, int>} */
    public static function count(string $branch): array
    {
        $breakdown = [
            'jobs' => PrintJob::query()->where('branch', $branch)->count(),
            'clients' => Client::query()->where('branch', $branch)->count(),
            'staff' => Staff::query()->where('branch', $branch)->count(),
            'users' => User::query()->where('branch', $branch)->count(),
            'saleslog' => SalesLog::query()->where('branch', $branch)->count(),
            'quotes' => Quote::query()->where('branch', $branch)->count(),
            'leads' => Lead::query()->where('branch', $branch)->count(),
            'inventory' => InventoryItem::query()->where('branch', $branch)->count(),
            'opex' => OpexEntry::query()->where('branch', $branch)->count(),
            'pettycash' => PettyCashEntry::query()->where('branch', $branch)->count(),
            'assets' => Asset::query()->where('branch', $branch)->count(),
            'procurement' => ProcurementEntry::query()->where('branch', $branch)->count(),
        ];

        return [
            'total' => array_sum($breakdown),
            'breakdown' => $breakdown,
        ];
    }

    public static function rename(string $from, string $to): void
    {
        $models = [
            PrintJob::class,
            Client::class,
            Staff::class,
            User::class,
            SalesLog::class,
            Quote::class,
            Lead::class,
            InventoryItem::class,
            OpexEntry::class,
            PettyCashEntry::class,
            Asset::class,
            ProcurementEntry::class,
        ];

        foreach ($models as $model) {
            $model::query()->where('branch', $from)->update(['branch' => $to]);
        }
    }
}
