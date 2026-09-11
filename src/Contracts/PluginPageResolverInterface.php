<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Resolves an Inertia page component name (e.g. "user/Index") to the
 * plugin-owned source file that actually contains it, when that page was
 * registered by a plugin rather than the host application.
 */
interface PluginPageResolverInterface
{
    /**
     * The page's source file path, relative to the application base path
     * and using forward slashes — matching the key Vite's build manifest
     * uses for that file — or null when the component does not belong to
     * any plugin (a host-owned page under "resources/js/pages").
     */
    public function sourcePath(string $component): ?string;
}
