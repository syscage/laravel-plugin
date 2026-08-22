<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\Exceptions\PluginAlreadyInstalledException;

/**
 * Installs a discovered plugin: creates its database record, assigns its
 * UUID, and invokes its install() lifecycle hook.
 */
final class PluginInstallCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:install {alias : The plugin alias}';

    protected $description = 'Install a discovered plugin';

    public function handle(PluginManagerInterface $manager, PluginLifecycleInterface $lifecycle): int
    {
        $plugin = $this->resolvePlugin($manager, (string) $this->argument('alias'));

        if ($plugin === null) {
            return self::FAILURE;
        }

        try {
            $lifecycle->install($plugin);
        } catch (PluginAlreadyInstalledException|MissingPluginDependencyException|ConflictingPluginException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->alias()}] installed.");

        return self::SUCCESS;
    }
}
