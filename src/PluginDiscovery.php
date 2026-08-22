<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginAutoloaderInterface;
use Syscage\Plugin\Contracts\PluginCacheInterface;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManifestInterface;
use Syscage\Plugin\Contracts\PluginManifestRepositoryInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\Exceptions\PluginClassNotFoundException;
use Syscage\Plugin\Support\PluginManifest;

/**
 * Scans the plugins directory for "plugin.json" manifests, registers each
 * plugin's autoloading, and resolves its "Plugin" class into the registry.
 */
final class PluginDiscovery implements PluginDiscoveryInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly PluginManifestRepositoryInterface $manifests,
        private readonly PluginAutoloaderInterface $autoloader,
        private readonly PluginCacheInterface $cache,
        private readonly PluginRegistryInterface $registry,
        private readonly string $pluginsPath,
        private readonly string $manifestFilename,
    ) {
    }

    public function discover(bool $fresh = false): array
    {
        $plugins = (! $fresh && $this->cache->exists())
            ? $this->discoverFromCache()
            : $this->discoverFromFilesystem();

        foreach ($plugins as $plugin) {
            $this->registry->register($plugin);
        }

        return $plugins;
    }

    /**
     * @return array<string, PluginInterface>
     */
    private function discoverFromCache(): array
    {
        $plugins = [];

        foreach ($this->cache->get() as $alias => $entry) {
            $manifest = PluginManifest::fromArray($entry['manifest']);
            $plugins[$alias] = $this->resolvePlugin($manifest, $entry['path']);
        }

        return $plugins;
    }

    /**
     * @return array<string, PluginInterface>
     */
    private function discoverFromFilesystem(): array
    {
        $plugins = [];
        $cacheable = [];

        foreach ($this->pluginDirectories() as $path) {
            $manifestPath = $path . DIRECTORY_SEPARATOR . $this->manifestFilename;

            if (! $this->manifests->exists($manifestPath)) {
                continue;
            }

            $manifest = $this->manifests->read($manifestPath);
            $plugins[$manifest->alias()] = $this->resolvePlugin($manifest, $path);

            $cacheable[$manifest->alias()] = [
                'manifest' => $manifest->toArray(),
                'path' => $path,
            ];
        }

        $this->cache->put($cacheable);

        return $plugins;
    }

    /**
     * @return array<int, string>
     */
    private function pluginDirectories(): array
    {
        if (! $this->files->isDirectory($this->pluginsPath)) {
            return [];
        }

        return $this->files->directories($this->pluginsPath);
    }

    private function resolvePlugin(PluginManifestInterface $manifest, string $path): PluginInterface
    {
        $namespace = $manifest->namespace();
        $class = rtrim($namespace, '\\') . '\\Plugin';
        $srcPath = $path . DIRECTORY_SEPARATOR . 'src';

        // The plugin's root namespace resolves against both "src/" (where
        // "Plugin.php" itself lives) and "src/app/" (where Http, Models,
        // Providers, etc. live), matching the standard plugin directory
        // layout in which "app/" is a physical grouping folder that is not
        // reflected in the namespace path.
        $this->autoloader->register($namespace, $srcPath);
        $this->autoloader->register($namespace, $srcPath . DIRECTORY_SEPARATOR . 'app');

        if (! class_exists($class)) {
            throw PluginClassNotFoundException::forClass($class, $manifest->alias());
        }

        $instance = new $class($manifest, $path);

        if (! $instance instanceof PluginInterface) {
            throw PluginClassNotFoundException::invalidType($class, $manifest->alias());
        }

        return $instance;
    }
}
