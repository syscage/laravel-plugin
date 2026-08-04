<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Support\ServiceProvider;
use Syscage\Plugin\Contracts\PluginAssetManagerInterface;
use Syscage\Plugin\Contracts\PluginCommandManagerInterface;
use Syscage\Plugin\Contracts\PluginConfigManagerInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginMigrationManagerInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\Contracts\PluginRouteManagerInterface;
use Syscage\Plugin\Contracts\PluginTranslationManagerInterface;
use Syscage\Plugin\Contracts\PluginViewManagerInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * Base class for every generated plugin's own service provider (the class
 * named by its "plugin.json" "provider" field).
 *
 * Laravel instantiates a registered provider with only the application
 * instance (`new $provider($app)`), so this class cannot be constructor-
 * injected with the specific {@see PluginInterface} it belongs to. Instead
 * it resolves itself by finding the plugin, in the already-populated
 * registry, whose manifest `provider()` matches its own class name — which
 * is always true by construction, since that value is exactly what
 * `plugin.json` declares.
 *
 * Subclasses may override {@see boot()} to register additional services,
 * as long as they call `parent::boot()`.
 */
abstract class PluginServiceProviderBase extends ServiceProvider
{
    private ?PluginInterface $resolvedPlugin = null;

    public function register(): void
    {
        $this->app->make(PluginConfigManagerInterface::class)->register($this->plugin());
    }

    public function boot(): void
    {
        $plugin = $this->plugin();

        $this->app->make(PluginRouteManagerInterface::class)->register($plugin);
        $this->app->make(PluginViewManagerInterface::class)->register($plugin);
        $this->app->make(PluginTranslationManagerInterface::class)->register($plugin);
        $this->app->make(PluginMigrationManagerInterface::class)->register($plugin);
        $this->app->make(PluginAssetManagerInterface::class)->register($plugin);
        $this->app->make(PluginCommandManagerInterface::class)->register($plugin);
    }

    /**
     * The plugin this service provider belongs to.
     *
     * @throws PluginNotFoundException
     */
    protected function plugin(): PluginInterface
    {
        if ($this->resolvedPlugin !== null) {
            return $this->resolvedPlugin;
        }

        $registry = $this->app->make(PluginRegistryInterface::class);

        foreach ($registry->all() as $candidate) {
            if ($candidate->provider() === static::class) {
                return $this->resolvedPlugin = $candidate;
            }
        }

        throw PluginNotFoundException::forProvider(static::class);
    }
}
