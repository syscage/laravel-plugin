<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Publishes a plugin's "src/public" assets so they are reachable from the
 * host application's public directory.
 */
interface PluginAssetManagerInterface
{
    /**
     * Link (or copy) the plugin's "src/public" into the public directory.
     *
     * @return bool False when there is no "src/public" directory or the
     *              link/copy could not be created.
     */
    public function register(PluginInterface $plugin): bool;

    /**
     * Remove a plugin's published assets (the symlink or copied directory
     * created by {@see register()}) by alias, without requiring the plugin
     * itself to still be resolvable.
     */
    public function unregister(string $alias): void;
}
