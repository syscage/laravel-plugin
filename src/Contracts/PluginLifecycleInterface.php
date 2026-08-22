<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\Exceptions\PluginAlreadyInstalledException;
use Syscage\Plugin\Exceptions\PluginNotInstalledException;

/**
 * Coordinates the install / uninstall / enable / disable / update
 * transitions of a plugin: persisting state to the database, syncing
 * "plugin.json", invoking the plugin's own lifecycle hooks, and
 * dispatching lifecycle events.
 */
interface PluginLifecycleInterface
{
    /**
     * @throws PluginAlreadyInstalledException
     * @throws MissingPluginDependencyException
     * @throws ConflictingPluginException
     */
    public function install(PluginInterface $plugin): void;

    /**
     * @throws PluginNotInstalledException
     */
    public function uninstall(PluginInterface $plugin): void;

    /**
     * @throws PluginNotInstalledException
     * @throws MissingPluginDependencyException
     * @throws ConflictingPluginException
     */
    public function enable(PluginInterface $plugin): void;

    /**
     * @throws PluginNotInstalledException
     */
    public function disable(PluginInterface $plugin): void;

    /**
     * @throws PluginNotInstalledException
     */
    public function update(PluginInterface $plugin, string $oldVersion): void;
}
