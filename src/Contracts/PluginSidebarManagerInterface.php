<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Merges the rows each plugin's SideBarSeeder stored in the "sidebar" table
 * into a single navigation manifest, and compiles it to a cache file so it does not need
 * to be rebuilt on every request.
 */
interface PluginSidebarManagerInterface
{
    /**
     * Merge the sidebar rows of the given plugins, keyed by alias.
     * Plugins without sidebar rows are omitted.
     *
     * @param array<string, PluginInterface> $plugins
     *
     * @return array<string, array<string, mixed>>
     */
    public function build(array $plugins): array;

    /**
     * Merge and persist the sidebar manifest to the compiled cache file.
     *
     * @param array<string, PluginInterface> $plugins
     *
     * @return array<string, array<string, mixed>>
     */
    public function cache(array $plugins): array;

    /**
     * Retrieve the compiled sidebar manifest, if one has been cached.
     *
     * @return array<string, array<string, mixed>>
     */
    public function get(): array;

    /**
     * Remove the compiled sidebar cache, if one exists.
     */
    public function forget(): void;
}
