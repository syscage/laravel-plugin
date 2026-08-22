<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Generates the compiled frontend manifest ("bootstrap/cache/plugins.ts")
 * consumed by React/Vue/Inertia frontends: each plugin's page component
 * imports, routes, and sidebar entries.
 */
interface FrontendManifestGeneratorInterface
{
    /**
     * Build and persist the manifest for the given plugins, returning its
     * generated contents.
     *
     * @param array<string, PluginInterface> $plugins
     */
    public function generate(array $plugins): string;
}
