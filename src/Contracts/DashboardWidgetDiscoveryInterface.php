<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Reads the "widgets" manifest entry of the given plugins, resolves each
 * declared class into a {@see DashboardWidgetInterface} instance, and
 * registers it with the widget registry.
 */
interface DashboardWidgetDiscoveryInterface
{
    /**
     * Discover every widget contributed by the given plugins.
     *
     * When `$fresh` is false and a discovery cache exists, the cache is
     * used instead of re-reading every plugin's manifest.
     *
     * @param array<string, PluginInterface> $plugins
     *
     * @return array<string, DashboardWidgetInterface> Widgets keyed by id.
     */
    public function discover(array $plugins, bool $fresh = false): array;
}
