<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Contracts\Container\Container;
use Syscage\Plugin\Contracts\DashboardWidgetCacheInterface;
use Syscage\Plugin\Contracts\DashboardWidgetDiscoveryInterface;
use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRegistryInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Exceptions\DashboardWidgetClassNotFoundException;

/**
 * Default implementation of {@see DashboardWidgetDiscoveryInterface}.
 */
final class DashboardWidgetDiscovery implements DashboardWidgetDiscoveryInterface
{
    public function __construct(
        private readonly Container $container,
        private readonly DashboardWidgetCacheInterface $cache,
        private readonly DashboardWidgetRegistryInterface $registry,
    ) {
    }

    public function discover(array $plugins, bool $fresh = false): array
    {
        $widgets = (! $fresh && $this->cache->exists())
            ? $this->discoverFromCache()
            : $this->discoverFromPlugins($plugins);

        foreach ($widgets as $widget) {
            $this->registry->register($widget);
        }

        return $widgets;
    }

    /**
     * @return array<string, DashboardWidgetInterface>
     */
    private function discoverFromCache(): array
    {
        $widgets = [];

        foreach ($this->cache->get() as $entry) {
            $widget = $this->resolveWidget($entry['class'], $entry['plugin']);
            $widgets[$widget->id()] = $widget;
        }

        return $widgets;
    }

    /**
     * @param array<string, PluginInterface> $plugins
     *
     * @return array<string, DashboardWidgetInterface>
     */
    private function discoverFromPlugins(array $plugins): array
    {
        $widgets = [];
        $cacheable = [];

        foreach ($plugins as $plugin) {
            foreach ($plugin->widgets() as $class) {
                $widget = $this->resolveWidget($class, $plugin->alias());
                $widgets[$widget->id()] = $widget;

                $cacheable[] = ['class' => $class, 'plugin' => $plugin->alias()];
            }
        }

        $this->cache->put($cacheable);

        return $widgets;
    }

    private function resolveWidget(string $class, string $pluginAlias): DashboardWidgetInterface
    {
        if (! class_exists($class)) {
            throw DashboardWidgetClassNotFoundException::forClass($class, $pluginAlias);
        }

        $instance = $this->container->make($class);

        if (! $instance instanceof DashboardWidgetInterface) {
            throw DashboardWidgetClassNotFoundException::invalidType($class, $pluginAlias);
        }

        return $instance;
    }
}
