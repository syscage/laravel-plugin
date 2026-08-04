<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Represents a single plugin: its manifest data, its filesystem layout, and
 * the lifecycle hooks the framework invokes during install, uninstall,
 * enable, disable, and update operations.
 *
 * All managers and commands must depend on this contract instead of
 * building plugin paths by hand.
 */
interface PluginInterface extends PluginManifestInterface
{
    /**
     * The plugin's absolute root directory path.
     */
    public function path(string $append = ''): string;

    /**
     * The plugin's "src" directory path.
     */
    public function srcPath(string $append = ''): string;

    /**
     * The plugin's "src/app" directory path.
     */
    public function appPath(string $append = ''): string;

    /**
     * The plugin's "src/config" directory path.
     */
    public function configPath(string $append = ''): string;

    /**
     * The plugin's "src/database" directory path.
     */
    public function databasePath(string $append = ''): string;

    /**
     * The plugin's "src/database/migrations" directory path.
     */
    public function migrationPath(string $append = ''): string;

    /**
     * The plugin's "src/database/factories" directory path.
     */
    public function factoryPath(string $append = ''): string;

    /**
     * The plugin's "src/database/seeders" directory path.
     */
    public function seederPath(string $append = ''): string;

    /**
     * The plugin's "src/lang" directory path.
     */
    public function langPath(string $append = ''): string;

    /**
     * The plugin's "src/resources" directory path.
     */
    public function resourcePath(string $append = ''): string;

    /**
     * The plugin's "src/resources/views" directory path.
     */
    public function viewPath(string $append = ''): string;

    /**
     * The plugin's "src/routes" directory path.
     */
    public function routePath(string $append = ''): string;

    /**
     * The plugin's "src/public" directory path.
     */
    public function publicPath(string $append = ''): string;

    /**
     * Invoked when the plugin is installed for the first time.
     */
    public function install(): void;

    /**
     * Invoked when the plugin is permanently uninstalled.
     */
    public function uninstall(): void;

    /**
     * Invoked when the plugin transitions from disabled to enabled.
     */
    public function enable(): void;

    /**
     * Invoked when the plugin transitions from enabled to disabled.
     */
    public function disable(): void;

    /**
     * Invoked when the plugin is updated from a previous version.
     */
    public function update(string $oldVersion): void;
}
