<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\PluginProviderNotFoundException;

/**
 * Boots plugins by registering their declared service provider with the
 * application container.
 */
interface PluginLoaderInterface
{
    /**
     * Register the given plugin's service provider, if the plugin is
     * enabled.
     *
     * @throws PluginProviderNotFoundException
     */
    public function load(PluginInterface $plugin): void;

    /**
     * Load every plugin in the given collection, in order.
     *
     * @param array<string, PluginInterface> $plugins
     */
    public function loadMany(array $plugins): void;
}
