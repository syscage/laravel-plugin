<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginPageResolverInterface;

/**
 * Default implementation of {@see PluginPageResolverInterface}.
 *
 * Mirrors the "{alias}/{PageName}" key convention {@see FrontendManifestGenerator}
 * uses when it scans each plugin's "resources/js/Pages" directory, so a page
 * key produced there resolves back to the exact same source file here.
 */
final class PluginPageResolver implements PluginPageResolverInterface
{
    private const PAGE_EXTENSIONS = ['tsx', 'ts', 'jsx', 'vue'];

    public function __construct(
        private readonly Filesystem $files,
        private readonly PluginManagerInterface $plugins,
        private readonly string $basePath,
    ) {
    }

    public function sourcePath(string $component): ?string
    {
        foreach ($this->plugins->enabled() as $alias => $plugin) {
            $prefix = $alias . '/';

            if (! str_starts_with($component, $prefix)) {
                continue;
            }

            $pagesDirectory = $plugin->resourcePath('js' . DIRECTORY_SEPARATOR . 'Pages');
            $relative = substr($component, strlen($prefix));

            foreach (self::PAGE_EXTENSIONS as $extension) {
                $candidate = $pagesDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.' . $extension;

                if ($this->files->isFile($candidate)) {
                    return $this->relativeToBasePath($candidate);
                }
            }
        }

        return null;
    }

    private function relativeToBasePath(string $path): string
    {
        $base = str_replace('\\', '/', rtrim($this->basePath, '/\\')) . '/';
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }
}
