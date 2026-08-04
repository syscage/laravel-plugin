<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginLoaderInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;

/**
 * Default implementation of {@see PluginManagerInterface}, coordinating
 * discovery, dependency resolution, and loading.
 */
final class PluginManager implements PluginManagerInterface
{
    public function __construct(
        private readonly PluginDiscoveryInterface $discovery,
        private readonly PluginRegistryInterface $registry,
        private readonly PluginDependencyResolverInterface $resolver,
        private readonly PluginLoaderInterface $loader,
    ) {
    }

    public function boot(bool $fresh = false): void
    {
        $this->discovery->discover($fresh);

        $ordered = $this->resolver->resolve($this->registry->enabled());

        $this->loader->loadMany($ordered);
    }

    public function all(): array
    {
        return $this->registry->all();
    }

    public function enabled(): array
    {
        return $this->registry->enabled();
    }

    public function has(string $alias): bool
    {
        return $this->registry->has($alias);
    }

    public function find(string $alias): PluginInterface
    {
        return $this->registry->get($alias);
    }
}
