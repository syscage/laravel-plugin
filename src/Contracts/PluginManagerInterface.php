<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * The top-level entry point for discovering and booting plugins.
 */
interface PluginManagerInterface
{
    /**
     * Discover every plugin, validate/order the enabled ones, and load
     * their service providers.
     */
    public function boot(bool $fresh = false): void;

    /**
     * All known plugins, keyed by alias.
     *
     * @return array<string, PluginInterface>
     */
    public function all(): array;

    /**
     * All enabled plugins, keyed by alias, ordered by priority.
     *
     * @return array<string, PluginInterface>
     */
    public function enabled(): array;

    /**
     * Determine whether a plugin with the given alias is known.
     */
    public function has(string $alias): bool;

    /**
     * Find a plugin by its alias.
     *
     * @throws PluginNotFoundException
     */
    public function find(string $alias): PluginInterface;
}
