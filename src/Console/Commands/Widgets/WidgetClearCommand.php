<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Widgets;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\DashboardWidgetCacheInterface;

/**
 * Removes the compiled dashboard widget discovery cache.
 */
final class WidgetClearCommand extends Command
{
    protected $signature = 'widget:clear';

    protected $description = 'Clear the dashboard widget discovery cache';

    public function handle(DashboardWidgetCacheInterface $cache): int
    {
        $cache->forget();

        $this->components->info('Dashboard widget cache cleared successfully.');

        return self::SUCCESS;
    }
}
