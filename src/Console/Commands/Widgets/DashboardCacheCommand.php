<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Widgets;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\DashboardManagerInterface;

/**
 * Rebuilds the compiled default dashboard layout cache.
 */
final class DashboardCacheCommand extends Command
{
    protected $signature = 'dashboard:cache';

    protected $description = 'Build the default dashboard layout cache';

    public function handle(DashboardManagerInterface $dashboard): int
    {
        $dashboard->cacheDefaultLayout();

        $this->components->info('Default dashboard layout cache built successfully.');

        return self::SUCCESS;
    }
}
