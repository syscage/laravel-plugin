<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Scans the plugins directory, reads each plugin's manifest, and resolves
 * a {@see PluginInterface} instance for each one found.
 */
interface PluginDiscoveryInterface
{
    /**
     * Discover every plugin under the configured plugins path.
     *
     * When `$fresh` is false and a discovery cache exists, the cache is
     * used instead of scanning the filesystem.
     *
     * @return array<string, PluginInterface> Plugins keyed by alias.
     */
    public function discover(bool $fresh = false): array;
}
