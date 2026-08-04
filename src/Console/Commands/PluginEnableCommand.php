<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;

/**
 * Enables an installed plugin.
 */
final class PluginEnableCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:enable {alias : The plugin alias}';

    protected $description = 'Enable an installed plugin';

    public function handle(PluginManagerInterface $manager, PluginLifecycleInterface $lifecycle): int
    {
        $plugin = $this->resolvePlugin($manager, (string) $this->argument('alias'));

        if ($plugin === null) {
            return self::FAILURE;
        }

        try {
            $lifecycle->enable($plugin);
        } catch (PluginNotInstalledException|MissingPluginDependencyException|ConflictingPluginException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->alias()}] enabled.");

        return self::SUCCESS;
    }
}
