<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManifestInterface;

/**
 * Base class for every generated plugin's "src/Plugin.php".
 *
 * Delegates manifest accessors to the parsed {@see PluginManifestInterface}
 * and computes every filesystem path from the plugin's root directory,
 * following the standard plugin directory layout. Concrete plugins may
 * override {@see install()}, {@see uninstall()}, {@see enable()},
 * {@see disable()}, and {@see update()} to hook into their own lifecycle.
 */
abstract class AbstractPlugin implements PluginInterface
{
    public function __construct(
        protected readonly PluginManifestInterface $manifest,
        protected readonly string $basePath,
    ) {
    }

    public function id(): ?string
    {
        return $this->manifest->id();
    }

    public function name(): string
    {
        return $this->manifest->name();
    }

    public function alias(): string
    {
        return $this->manifest->alias();
    }

    public function description(): string
    {
        return $this->manifest->description();
    }

    public function version(): string
    {
        return $this->manifest->version();
    }

    public function provider(): string
    {
        return $this->manifest->provider();
    }

    public function namespace(): string
    {
        return $this->manifest->namespace();
    }

    public function priority(): int
    {
        return $this->manifest->priority();
    }

    public function enabled(): bool
    {
        return $this->manifest->enabled();
    }

    public function requires(): array
    {
        return $this->manifest->requires();
    }

    public function conflicts(): array
    {
        return $this->manifest->conflicts();
    }

    public function permissions(): array
    {
        return $this->manifest->permissions();
    }

    public function sidebar(): array
    {
        return $this->manifest->sidebar();
    }

    public function authors(): array
    {
        return $this->manifest->authors();
    }

    public function tablePrefix(): ?string
    {
        return $this->manifest->tablePrefix();
    }

    public function toArray(): array
    {
        return $this->manifest->toArray();
    }

    public function path(string $append = ''): string
    {
        return $this->joinPaths($this->basePath, $append);
    }

    public function srcPath(string $append = ''): string
    {
        return $this->joinPaths($this->path('src'), $append);
    }

    public function appPath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('app'), $append);
    }

    public function configPath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('config'), $append);
    }

    public function databasePath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('database'), $append);
    }

    public function migrationPath(string $append = ''): string
    {
        return $this->joinPaths($this->databasePath('migrations'), $append);
    }

    public function factoryPath(string $append = ''): string
    {
        return $this->joinPaths($this->databasePath('factories'), $append);
    }

    public function seederPath(string $append = ''): string
    {
        return $this->joinPaths($this->databasePath('seeders'), $append);
    }

    public function langPath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('lang'), $append);
    }

    public function resourcePath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('resources'), $append);
    }

    public function viewPath(string $append = ''): string
    {
        return $this->joinPaths($this->resourcePath('views'), $append);
    }

    public function routePath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('routes'), $append);
    }

    public function publicPath(string $append = ''): string
    {
        return $this->joinPaths($this->srcPath('public'), $append);
    }

    public function install(): void
    {
        //
    }

    public function uninstall(): void
    {
        //
    }

    public function enable(): void
    {
        //
    }

    public function disable(): void
    {
        //
    }

    public function update(string $oldVersion): void
    {
        //
    }

    private function joinPaths(string $base, string $append): string
    {
        if ($append === '') {
            return $base;
        }

        return rtrim($base, '/\\') . DIRECTORY_SEPARATOR . ltrim($append, '/\\');
    }
}
