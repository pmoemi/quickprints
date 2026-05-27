<?php

namespace App\Services;

use App\Models\BmsSetting;
use App\Support\BmsSettingsDefaults;
use Database\Seeders\BmsDemoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BmsDataResetService
{
    /** @var list<string> */
    private const OPERATIONAL_TABLES = [
        'audit_logs',
        'bms_notifications',
        'bms_messages',
        'portal_tokens',
        'bank_statement_lines',
        'ledger_entries',
        'attendance_records',
        'leave_requests',
        'payroll_entries',
        'petty_cash_entries',
        'opex_entries',
        'procurement_entries',
        'purchase_orders',
        'recurring_bills',
        'suppliers',
        'assets',
        'sales_logs',
        'print_jobs',
        'quotes',
        'leads',
        'inventory_items',
        'clients',
        'staff',
    ];

    /** @return array<string, int> */
    public function recordCounts(): array
    {
        $counts = [];
        foreach (self::OPERATIONAL_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $counts[$table] = (int) DB::table($table)->count();
        }

        return $counts;
    }

    public function totalRecords(): int
    {
        return array_sum($this->recordCounts());
    }

    /**
     * Remove operational BMS records. Settings, service catalogue, and user accounts are kept.
     *
     * @return array<string, int> Rows removed per table
     */
    public function clearRecords(bool $includeAudit = true): array
    {
        $tables = self::OPERATIONAL_TABLES;
        if (! $includeAudit) {
            $tables = array_values(array_filter($tables, fn (string $t) => $t !== 'audit_logs'));
        }

        return $this->truncateTables($tables);
    }

    /** Load or refresh demo sample data (upserts). */
    public function seedDemo(): void
    {
        Artisan::call('db:seed', ['--class' => BmsDemoSeeder::class, '--force' => true]);
    }

    /**
     * Clear all operational records then load demo data.
     *
     * @return array{cleared: array<string, int>, seeded: true}
     */
    public function resetToDemo(bool $includeAudit = true): array
    {
        $cleared = $this->clearRecords($includeAudit);
        $this->seedDemo();

        return ['cleared' => $cleared, 'seeded' => true];
    }

    /** Reset BMS settings row to factory defaults (does not touch records). */
    public function resetSettingsToDefaults(): void
    {
        BmsSetting::query()->updateOrCreate(
            ['id' => 1],
            ['data' => BmsSettingsDefaults::all()]
        );
    }

    /**
     * @param  list<string>  $tables
     * @return array<string, int>
     */
    private function truncateTables(array $tables): array
    {
        $removed = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $removed[$table] = (int) DB::table($table)->count();
                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return $removed;
    }
}
