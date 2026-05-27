<?php

namespace App\Console\Commands;

use App\Support\BmsFrontendInstaller;
use Illuminate\Console\Command;

class InstallBmsFrontend extends Command
{
    protected $signature = 'bms:install-frontend';

    protected $description = 'Copy QuickPrints_BMS_Offline.html to public/bms/index.html';

    public function handle(): int
    {
        if (! BmsFrontendInstaller::sourceExists()) {
            $this->error('Source file not found: '.BmsFrontendInstaller::sourcePath());
            $this->line('Place QuickPrints_BMS_Offline.html in the project root, then run this command again.');

            return self::FAILURE;
        }

        BmsFrontendInstaller::install();

        $this->info('Frontend installed: '.BmsFrontendInstaller::targetPath());
        $this->line('Open: '.rtrim(config('app.url'), '/').'/bms');

        return self::SUCCESS;
    }
}
