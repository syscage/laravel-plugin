<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Persists the result of dashboard widget discovery to a compiled cache
 * file so subsequent requests can skip re-reading every plugin manifest.
 */
interface DashboardWidgetCacheInterface
{
    /**
     * Determine whether a discovery cache currently exists.
     */
    public function exists(): bool;

    /**
     * Retrieve the cached discovery data.
     *
     * @return array<int, array{class: string, plugin: string}>
     */
    public function get(): array;

    /**
     * Persist the given discovery data.
     *
     * @param array<int, array{class: string, plugin: string}> $widgets
     */
    public function put(array $widgets): void;

    /**
     * Remove the discovery cache, if one exists.
     */
    public function forget(): void;
}
