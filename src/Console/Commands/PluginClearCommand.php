<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\PluginCacheInterface;
use Syscage\Plugin\Contracts\PluginSidebarManagerInterface;

/**
 * Removes every compiled plugin cache.
 */
final class PluginClearCommand extends Command
{
    protected $signature = 'plugin:clear';

    protected $description = 'Clear the plugin discovery and sidebar caches';

    public function handle(PluginCacheInterface $cache, PluginSidebarManagerInterface $sidebar): int
    {
        $cache->forget();
        $sidebar->forget();

        $this->components->info('Plugin caches cleared successfully.');

        return self::SUCCESS;
    }
}
