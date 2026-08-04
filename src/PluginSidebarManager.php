<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginSidebarManagerInterface;

/**
 * Default implementation of {@see PluginSidebarManagerInterface}.
 */
final class PluginSidebarManager implements PluginSidebarManagerInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $cachePath,
    ) {
    }

    public function build(array $plugins): array
    {
        $sidebar = [];

        foreach ($plugins as $alias => $plugin) {
            if ($plugin->sidebar() !== []) {
                $sidebar[$alias] = $plugin->sidebar();
            }
        }

        return $sidebar;
    }

    public function cache(array $plugins): array
    {
        $sidebar = $this->build($plugins);

        $directory = dirname($this->cachePath);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, recursive: true);
        }

        $export = var_export($sidebar, true);

        $this->files->put($this->cachePath, "<?php\n\nreturn {$export};\n");

        return $sidebar;
    }

    public function get(): array
    {
        if (! $this->files->isFile($this->cachePath)) {
            return [];
        }

        return (array) require $this->cachePath;
    }

    public function forget(): void
    {
        if ($this->files->isFile($this->cachePath)) {
            $this->files->delete($this->cachePath);
        }
    }
}
