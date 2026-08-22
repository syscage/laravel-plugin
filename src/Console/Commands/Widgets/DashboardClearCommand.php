<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Widgets;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\DashboardManagerInterface;

/**
 * Removes the compiled default dashboard layout cache.
 */
final class DashboardClearCommand extends Command
{
    protected $signature = 'dashboard:clear';

    protected $description = 'Clear the default dashboard layout cache';

    public function handle(DashboardManagerInterface $dashboard): int
    {
        $dashboard->forgetDefaultLayoutCache();

        $this->components->info('Default dashboard layout cache cleared successfully.');

        return self::SUCCESS;
    }
}
