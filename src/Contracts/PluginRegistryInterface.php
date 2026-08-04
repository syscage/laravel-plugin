<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * In-memory collection of the plugins known to the current request.
 */
interface PluginRegistryInterface
{
    /**
     * Register a plugin, keyed by its alias.
     */
    public function register(PluginInterface $plugin): void;

    /**
     * Remove a plugin from the registry.
     */
    public function forget(string $alias): void;

    /**
     * Determine whether a plugin with the given alias is registered.
     */
    public function has(string $alias): bool;

    /**
     * Retrieve a registered plugin by its alias.
     *
     * @throws PluginNotFoundException
     */
    public function get(string $alias): PluginInterface;

    /**
     * All registered plugins, keyed by alias.
     *
     * @return array<string, PluginInterface>
     */
    public function all(): array;

    /**
     * All registered plugins with `enabled` set to true, keyed by alias,
     * ordered by ascending priority.
     *
     * @return array<string, PluginInterface>
     */
    public function enabled(): array;
}
