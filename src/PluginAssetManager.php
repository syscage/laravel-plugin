<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginAssetManagerInterface;
use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Default implementation of {@see PluginAssetManagerInterface}.
 *
 * Links (or, failing that, copies) a plugin's "src/public" directory into
 * the host application's public directory, the same mechanism Laravel's
 * own `storage:link` command uses via {@see Filesystem::link()} — so
 * plugin assets are reachable immediately, with no publish step required.
 */
final class PluginAssetManager implements PluginAssetManagerInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $publicPath,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        $source = $plugin->publicPath();

        if (! $this->files->isDirectory($source)) {
            return;
        }

        $target = rtrim($this->publicPath, '/\\') . DIRECTORY_SEPARATOR . $plugin->alias();

        if ($this->files->exists($target)) {
            return;
        }

        $this->files->ensureDirectoryExists(dirname($target));

        @$this->files->link($source, $target);

        if (! $this->files->exists($target)) {
            $this->files->copyDirectory($source, $target);
        }
    }
}
