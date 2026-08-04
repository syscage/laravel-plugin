<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginCacheInterface;

/**
 * Compiled-file cache of discovered plugin manifests, mirroring the pattern
 * Laravel itself uses for "bootstrap/cache/packages.php".
 */
final class PluginCache implements PluginCacheInterface
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

    public function put(array $plugins): void
    {
        $directory = dirname($this->path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, recursive: true);
        }

        $export = var_export($plugins, true);

        $this->files->put($this->path, "<?php\n\nreturn {$export};\n");
    }

    public function forget(): void
    {
        if ($this->exists()) {
            $this->files->delete($this->path);
        }
    }
}
