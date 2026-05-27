<?php

namespace App\Console\Commands;

use App\Services\BmsDataResetService;
use App\Support\DemoData;
use Illuminate\Console\Command;

class ResetBmsDataCommand extends Command
{
    protected $signature = 'bms:reset-data
                            {action=help : clear, seed, demo, or counts}
                            {--force : Run without confirmation}
                            {--keep-audit : Keep audit log entries when clearing}';

    protected $description = 'Clear BMS operational records or reload demo data';

    public function handle(BmsDataResetService $reset): int
    {
        $action = strtolower((string) $this->argument('action'));

        if ($action === 'help') {
            $this->printHelp();

            return self::SUCCESS;
        }

        if ($action === 'counts') {
            $this->printCounts($reset);

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirmAction($action)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $includeAudit = ! $this->option('keep-audit');

        return match ($action) {
            'clear' => $this->runClear($reset, $includeAudit),
            'seed' => $this->runSeed($reset),
            'demo' => $this->runDemo($reset, $includeAudit),
            default => $this->invalidAction($action),
        };
    }

    private function printHelp(): void
    {
        $this->info('BMS data reset commands:');
        $this->line('  php artisan bms:reset-data counts          Show record totals');
        $this->line('  php artisan bms:reset-data clear --force   Delete all operational records');
        $this->line('  php artisan bms:reset-data seed --force    Load / refresh demo sample data');
        $this->line('  php artisan bms:reset-data demo --force    Clear everything, then load demo data');
        $this->newLine();
        $this->line('Demo login after seed: '.DemoData::DEFAULT_ADMIN_EMAIL.' / '.DemoData::DEFAULT_ADMIN_PASSWORD);
        $this->line('Settings and user accounts are always preserved when clearing.');
    }

    private function printCounts(BmsDataResetService $reset): void
    {
        $counts = $reset->recordCounts();
        arsort($counts);

        $rows = [];
        foreach ($counts as $table => $count) {
            if ($count > 0) {
                $rows[] = [$table, number_format($count)];
            }
        }

        if ($rows === []) {
            $this->info('No operational records found.');

            return;
        }

        $this->table(['Table', 'Records'], $rows);
        $this->info('Total: '.number_format($reset->totalRecords()).' records');
    }

    private function confirmAction(string $action): bool
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force.');

            return false;
        }

        $message = match ($action) {
            'clear' => 'This will permanently delete ALL operational BMS records (jobs, clients, sales, finance, etc.). Settings and users are kept. Continue?',
            'seed' => 'This will load or refresh demo sample data (existing demo IDs will be overwritten). Continue?',
            'demo' => 'This will DELETE all operational records and reload demo data. Continue?',
            default => 'Continue?',
        };

        return $this->confirm($message, false);
    }

    private function runClear(BmsDataResetService $reset, bool $includeAudit): int
    {
        $removed = $reset->clearRecords($includeAudit);
        $total = array_sum($removed);

        $this->info("Cleared {$total} records from ".count($removed).' tables.');
        $this->printRemovedSummary($removed);

        return self::SUCCESS;
    }

    private function runSeed(BmsDataResetService $reset): int
    {
        $reset->seedDemo();
        $this->info('Demo data loaded.');
        $this->line('Login: '.DemoData::DEFAULT_ADMIN_EMAIL.' / '.DemoData::DEFAULT_ADMIN_PASSWORD);

        return self::SUCCESS;
    }

    private function runDemo(BmsDataResetService $reset, bool $includeAudit): int
    {
        $result = $reset->resetToDemo($includeAudit);
        $total = array_sum($result['cleared']);

        $this->info("Cleared {$total} records and loaded demo data.");
        $this->line('Login: '.DemoData::DEFAULT_ADMIN_EMAIL.' / '.DemoData::DEFAULT_ADMIN_PASSWORD);

        return self::SUCCESS;
    }

    /** @param array<string, int> $removed */
    private function printRemovedSummary(array $removed): void
    {
        arsort($removed);
        $top = array_slice($removed, 0, 8, true);
        foreach ($top as $table => $count) {
            if ($count > 0) {
                $this->line("  - {$table}: ".number_format($count));
            }
        }
    }

    private function invalidAction(string $action): int
    {
        $this->error("Unknown action \"{$action}\". Use clear, seed, demo, or counts.");

        return self::FAILURE;
    }
}
