<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;

/**
 * Uninstalls a plugin: invokes its uninstall() lifecycle hook and removes
 * its database record.
 */
final class PluginUninstallCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:uninstall {alias : The plugin alias} {--force : Skip the confirmation prompt}';

    protected $description = 'Uninstall a plugin';

    public function handle(PluginManagerInterface $manager, PluginLifecycleInterface $lifecycle): int
    {
        $alias = (string) $this->argument('alias');

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to uninstall [{$alias}]?")) {
            $this->components->warn('Uninstall cancelled.');

            return self::SUCCESS;
        }

        $plugin = $this->resolvePlugin($manager, $alias);

        if ($plugin === null) {
            return self::FAILURE;
        }

        try {
            $lifecycle->uninstall($plugin);
        } catch (PluginNotInstalledException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->alias()}] uninstalled.");

        return self::SUCCESS;
    }
}
