<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;

/**
 * Updates an installed plugin to the version currently declared in its
 * manifest, invoking its update() lifecycle hook with the previous
 * version.
 */
final class PluginUpdateCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:update {alias : The plugin alias}';

    protected $description = 'Update an installed plugin to its current manifest version';

    public function handle(
        PluginManagerInterface $manager,
        PluginLifecycleInterface $lifecycle,
        PluginRecordRepositoryInterface $records,
    ): int {
        $plugin = $this->resolvePlugin($manager, (string) $this->argument('alias'));

        if ($plugin === null) {
            return self::FAILURE;
        }

        $record = $records->findByAlias($plugin->alias());

        if ($record === null) {
            $this->components->error("Plugin [{$plugin->alias()}] is not installed.");

            return self::FAILURE;
        }

        if ($record->version === $plugin->version()) {
            $this->components->info("Plugin [{$plugin->alias()}] is already at version [{$plugin->version()}].");

            return self::SUCCESS;
        }

        $oldVersion = $record->version;

        try {
            $lifecycle->update($plugin, $oldVersion);
        } catch (PluginNotInstalledException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->alias()}] updated from [{$oldVersion}] to [{$plugin->version()}].");

        return self::SUCCESS;
    }
}
