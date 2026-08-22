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

    public function unregister(string $alias): void
    {
        $target = rtrim($this->publicPath, '/\\') . DIRECTORY_SEPARATOR . $alias;

        if (! file_exists($target)) {
            return;
        }

        // `Filesystem::deleteDirectory()` does not special-case its own
        // top-level argument being a link: it opens the directory and
        // deletes what it finds inside, which — for a symlink or a
        // Windows directory junction — means recursing straight through
        // into and deleting the plugin's real "src/public" source files.
        // A link must be removed directly instead, but `is_link()`/
        // `is_dir()` do not reliably identify a Windows junction created
        // by `Filesystem::link()`, so detection by type is unreliable.
        // rmdir() and unlink() are each safe no-ops against the "wrong"
        // kind of target (rmdir() refuses a non-empty real directory or a
        // file symlink; unlink() refuses a real directory), so trying
        // both first — before ever falling back to a recursive delete —
        // is safe regardless of what platform or link type is in play.
        if (@rmdir($target) || @unlink($target)) {
            return;
        }

        $this->files->deleteDirectory($target);
    }
}
