<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\CircularPluginDependencyException;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;

/**
 * Validates and orders a set of plugins according to their declared
 * "requires" and "conflicts" manifest entries.
 */
interface PluginDependencyResolverInterface
{
    /**
     * Validate every plugin's requirements and conflicts, then return them
     * ordered so that dependencies always precede dependents. Plugins with
     * no unresolved dependencies are ordered by ascending priority.
     *
     * @param array<string, PluginInterface> $plugins Plugins keyed by alias.
     *
     * @return array<string, PluginInterface>
     *
     * @throws MissingPluginDependencyException
     * @throws ConflictingPluginException
     * @throws CircularPluginDependencyException
     */
    public function resolve(array $plugins): array;

    /**
     * Validate a single plugin's requirements and conflicts against the
     * given set of plugins, without ordering.
     *
     * @param array<string, PluginInterface> $plugins Plugins keyed by alias.
     *
     * @throws MissingPluginDependencyException
     * @throws ConflictingPluginException
     */
    public function validate(PluginInterface $plugin, array $plugins): void;
}
