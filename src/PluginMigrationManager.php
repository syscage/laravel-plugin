<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginMigrationManagerInterface;

/**
 * Default implementation of {@see PluginMigrationManagerInterface}.
 */
final class PluginMigrationManager implements PluginMigrationManagerInterface
{
    public function __construct(
        private readonly Migrator $migrator,
        private readonly Filesystem $files,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        $path = $plugin->migrationPath();

        if ($this->files->isDirectory($path)) {
            $this->migrator->path($path);
        }
    }
}
