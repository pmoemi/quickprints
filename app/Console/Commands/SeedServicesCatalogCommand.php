<?php

namespace App\Console\Commands;

use App\Models\ServiceItem;
use App\Support\ServicesCatalog;
use Illuminate\Console\Command;

class SeedServicesCatalogCommand extends Command
{
    protected $signature = 'bms:seed-services
                            {--force : Run without confirmation}';

    protected $description = 'Seed the default services catalogue (Signages, Branding, Printing) without touching other data';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm(
            'Add missing default catalogue services? Existing services and all other data are kept.',
            true
        )) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $before = ServiceItem::query()->count();
        $added = ServicesCatalog::seedMissing();
        $after = ServiceItem::query()->count();

        $this->info("Services catalogue updated: {$added} added, {$after} total (was {$before}).");
        $this->line('Categories: Signages · Branding · Printing');
        $this->line('Expected catalogue size: '.ServicesCatalog::totalItems().' services.');

        if ($added === 0 && $after < ServicesCatalog::totalItems()) {
            $this->comment('Some catalogue items use different names/categories from an older list — add manually or rename in Services Catalogue.');
        }

        return self::SUCCESS;
    }
}
