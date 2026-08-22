<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;

/**
 * Disables an installed plugin.
 */
final class PluginDisableCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:disable {alias : The plugin alias}';

    protected $description = 'Disable an installed plugin';

    public function handle(PluginManagerInterface $manager, PluginLifecycleInterface $lifecycle): int
    {
        $plugin = $this->resolvePlugin($manager, (string) $this->argument('alias'));

        if ($plugin === null) {
            return self::FAILURE;
        }

        try {
            $lifecycle->disable($plugin);
        } catch (PluginNotInstalledException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->alias()}] disabled.");

        return self::SUCCESS;
    }
}
