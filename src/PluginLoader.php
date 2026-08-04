<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Foundation\Application;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginLoaderInterface;
use Syscage\Plugin\Exceptions\PluginProviderNotFoundException;

/**
 * Default implementation of {@see PluginLoaderInterface}.
 */
final class PluginLoader implements PluginLoaderInterface
{
    public function __construct(
        private readonly Application $app,
    ) {
    }

    public function load(PluginInterface $plugin): void
    {
        if (! $plugin->enabled()) {
            return;
        }

        $provider = $plugin->provider();

        if (! class_exists($provider)) {
            throw PluginProviderNotFoundException::forClass($provider, $plugin->alias());
        }

        $this->app->register($provider);
    }

    public function loadMany(array $plugins): void
    {
        foreach ($plugins as $plugin) {
            $this->load($plugin);
        }
    }
}
