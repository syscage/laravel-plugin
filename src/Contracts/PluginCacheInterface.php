<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Persists the result of plugin discovery to a compiled cache file so
 * subsequent requests can skip re-scanning the plugins directory.
 */
interface PluginCacheInterface
{
    /**
     * Determine whether a discovery cache currently exists.
     */
    public function exists(): bool;

    /**
     * Retrieve the cached discovery data.
     *
     * @return array<string, array{manifest: array<string, mixed>, path: string}>
     */
    public function get(): array;

    /**
     * Persist the given discovery data, keyed by plugin alias.
     *
     * @param array<string, array{manifest: array<string, mixed>, path: string}> $plugins
     */
    public function put(array $plugins): void;

    /**
     * Remove the discovery cache, if one exists.
     */
    public function forget(): void;
}
