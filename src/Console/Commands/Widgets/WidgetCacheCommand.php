<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Widgets;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\DashboardWidgetDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;

/**
 * Rebuilds the dashboard widget discovery cache from every enabled plugin's
 * manifest.
 */
final class WidgetCacheCommand extends Command
{
    protected $signature = 'widget:cache';

    protected $description = 'Build the dashboard widget discovery cache';

    public function handle(PluginManagerInterface $plugins, DashboardWidgetDiscoveryInterface $discovery): int
    {
        $discovery->discover($plugins->enabled(), fresh: true);

        $this->components->info('Dashboard widget cache built successfully.');

        return self::SUCCESS;
    }
}
