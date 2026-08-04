<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\FrontendDetectorInterface;
use Syscage\Plugin\Contracts\FrontendManifestGeneratorInterface;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginSidebarManagerInterface;

/**
 * Rebuilds every compiled plugin cache: the discovery cache, the merged
 * sidebar manifest, and — when the host uses a React/Vue/Inertia frontend
 * — the frontend page/route/sidebar manifest.
 */
final class PluginCacheCommand extends Command
{
    protected $signature = 'plugin:cache';

    protected $description = 'Build the plugin discovery, sidebar, and frontend caches';

    public function handle(
        PluginDiscoveryInterface $discovery,
        PluginSidebarManagerInterface $sidebar,
        FrontendDetectorInterface $frontendDetector,
        FrontendManifestGeneratorInterface $frontendManifest,
    ): int {
        $plugins = $discovery->discover(fresh: true);
        $sidebar->cache($plugins);

        if ($frontendDetector->detect() !== 'blade') {
            $frontendManifest->generate($plugins);
        }

        $this->components->info('Plugin caches built successfully.');

        return self::SUCCESS;
    }
}
