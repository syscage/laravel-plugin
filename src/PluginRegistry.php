<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * Default in-memory implementation of the plugin registry.
 */
final class PluginRegistry implements PluginRegistryInterface
{
    /**
     * @var array<string, PluginInterface>
     */
    private array $plugins = [];

    public function register(PluginInterface $plugin): void
    {
        $this->plugins[$plugin->alias()] = $plugin;
    }

    public function forget(string $alias): void
    {
        unset($this->plugins[$alias]);
    }

    public function has(string $alias): bool
    {
        return isset($this->plugins[$alias]);
    }

    public function get(string $alias): PluginInterface
    {
        return $this->plugins[$alias] ?? throw PluginNotFoundException::forAlias($alias);
    }

    public function all(): array
    {
        return $this->plugins;
    }

    public function enabled(): array
    {
        $enabled = array_filter($this->plugins, static fn (PluginInterface $plugin): bool => $plugin->enabled());

        uasort($enabled, static fn (PluginInterface $a, PluginInterface $b): int => $a->priority() <=> $b->priority());

        return $enabled;
    }
}
