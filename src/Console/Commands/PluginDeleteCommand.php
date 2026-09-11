<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginAssetManagerInterface;
use Syscage\Plugin\Contracts\PluginCacheInterface;
use Syscage\Plugin\Contracts\PluginLifecycleInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Contracts\PluginSidebarManagerInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * Permanently deletes a plugin: its database record, every compiled cache,
 * its published public assets, and every file under its own directory.
 *
 * Unlike {@see PluginUninstallCommand}, which only reverses the database
 * registration and leaves "plugins/{alias}/" untouched, this command
 * removes the plugin entirely. It is deliberately resilient to a plugin
 * that can no longer be resolved (e.g. its directory was already partially
 * or fully removed from disk by hand) so it can also be used to clean up
 * exactly that kind of inconsistent state.
 *
 * This does not roll back the plugin's own database migrations — dropping
 * tables a plugin created is a separate, data-destructive decision left to
 * `migrate:rollback-plugin`, run explicitly if that is really wanted.
 */
final class PluginDeleteCommand extends Command
{
    protected $signature = 'plugin:delete {alias : The plugin alias} {--force : Skip the confirmation prompt}';

    protected $description = 'Permanently delete a plugin: its database record, compiled caches, public assets, and files on disk';

    public function handle(
        PluginManagerInterface $manager,
        PluginLifecycleInterface $lifecycle,
        PluginRecordRepositoryInterface $records,
        PluginCacheInterface $cache,
        PluginSidebarManagerInterface $sidebar,
        PluginAssetManagerInterface $assets,
        Filesystem $files,
    ): int {
        $alias = (string) $this->argument('alias');

        if (! $this->option('force') && ! $this->confirm(
            "This will permanently delete [{$alias}]: its database record, every compiled cache, "
            . 'its published public assets, and every file under its plugin directory. '
            . 'This cannot be undone. Continue?',
        )) {
            $this->components->warn('Delete cancelled.');

            return self::SUCCESS;
        }

        $plugin = null;

        try {
            $plugin = $manager->find($alias);
        } catch (PluginNotFoundException) {
            // The plugin may already be partially or fully missing from
            // disk — deletion should still purge whatever remains of it
            // elsewhere (database record, caches, published assets).
        }

        if ($plugin !== null && $records->exists($alias)) {
            $lifecycle->uninstall($plugin);
        } elseif (($record = $records->findByAlias($alias)) !== null) {
            $this->components->warn(
                "The plugin class for [{$alias}] could not be resolved; deleting its database record "
                . 'without running its uninstall() hook.',
            );

            $records->delete($record);
        }

        $cache->forget();
        $sidebar->forget();
        $assets->unregister($alias);

        $path = $plugin?->path() ?? rtrim((string) config('plugin.plugins_path'), '/\\') . DIRECTORY_SEPARATOR . $alias;

        if ($files->isDirectory($path)) {
            $files->deleteDirectory($path);
        }

        $this->components->info("Plugin [{$alias}] permanently deleted.");

        return self::SUCCESS;
    }
}
