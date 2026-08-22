<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\DashboardWidgetCacheInterface;

/**
 * Compiled-file cache of discovered dashboard widget classes, mirroring
 * {@see \Syscage\Plugin\PluginCache}.
 */
final class DashboardWidgetCache implements DashboardWidgetCacheInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $path,
    ) {
    }

    public function exists(): bool
    {
        return $this->files->isFile($this->path);
    }

    public function get(): array
    {
        if (! $this->exists()) {
            return [];
        }

        return (array) require $this->path;
    }

    public function put(array $widgets): void
    {
        $directory = dirname($this->path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, recursive: true);
        }

        $export = var_export($widgets, true);

        $this->files->put($this->path, "<?php\n\nreturn {$export};\n");
    }

    public function forget(): void
    {
        if ($this->exists()) {
            $this->files->delete($this->path);
        }
    }
}
