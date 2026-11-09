<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
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

    public function __construct(
        private readonly PluginRecordRepositoryInterface $records,
    ) {
    }

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
        $enabled = array_intersect_key($this->plugins, array_flip($this->records->enabledAliases()));

        ksort($enabled);

        return $enabled;
    }
}
