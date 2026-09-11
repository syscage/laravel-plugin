<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Relocates wayfinder-generated route helpers that belong exclusively to a
 * plugin into that plugin's own directory, leaving a thin re-export in the
 * host application's `resources/js/routes/` so existing imports keep
 * working.
 */
interface PluginFrontendRouteLinkerInterface
{
    /**
     * Run right after `wayfinder:generate` has written its output: for
     * every directory under the host's routes path that belongs
     * exclusively to one of the given plugins, move its real generated
     * files into that plugin's own `src/resources/js/routes` (rewriting
     * each file's import of the shared wayfinder helper to still resolve
     * correctly from its new location), and replace the original files
     * with a small `export * from '...'` re-export. Safe to call
     * repeatedly — plugin route directories are treated as fully
     * disposable and are rebuilt from scratch on every call.
     *
     * @param iterable<PluginInterface> $plugins
     */
    public function register(iterable $plugins): void;
}
