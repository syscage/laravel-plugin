<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;

/**
 * Forces a fresh scan of the plugins directory, bypassing the discovery
 * cache, and reports what was found.
 */
final class PluginDiscoverCommand extends Command
{
    protected $signature = 'plugin:discover';

    protected $description = 'Scan the plugins directory and rebuild the discovery cache';

    public function handle(PluginDiscoveryInterface $discovery): int
    {
        $plugins = $discovery->discover(fresh: true);

        $count = count($plugins);

        $this->components->info("Discovered {$count} " . ($count === 1 ? 'plugin' : 'plugins') . '.');

        foreach ($plugins as $plugin) {
            $this->components->twoColumnDetail($plugin->alias(), $plugin->version());
        }

        return self::SUCCESS;
    }
}
