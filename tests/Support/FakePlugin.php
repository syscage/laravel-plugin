<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Support;

use Syscage\Plugin\Contracts\PluginInterface;

/**
 * A minimal, hand-rolled {@see PluginInterface} implementation for tests
 * that need fine-grained control over manifest values without the
 * ceremony of building a full manifest + filesystem fixture.
 */
final class FakePlugin implements PluginInterface
{
    /**
     * @param array<int, array<string, mixed>|string> $requires
     * @param array<int, string> $conflicts
     */
    public function __construct(
        private readonly string $alias,
        private readonly string $version = '1.0.0',
        private readonly int $priority = 100,
        private readonly bool $enabled = true,
        private readonly array $requires = [],
        private readonly array $conflicts = [],
        private readonly ?string $providerOverride = null,
        private readonly ?string $basePath = null,
        private readonly array $sidebarValue = [],
        private readonly ?string $tablePrefixValue = null,
    ) {
    }

    public function id(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return $this->alias;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function description(): string
    {
        return '';
    }

    public function version(): string
    {
        return $this->version;
    }

    public function provider(): string
    {
        return $this->providerOverride ?? static::class;
    }

    public function namespace(): string
    {
        return __NAMESPACE__;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function requires(): array
    {
        return $this->requires;
    }

    public function conflicts(): array
    {
        return $this->conflicts;
    }

    public function permissions(): array
    {
        return [];
    }

    public function sidebar(): array
    {
        return $this->sidebarValue;
    }

    public function authors(): array
    {
        return [];
    }

    public function tablePrefix(): ?string
    {
        return $this->tablePrefixValue;
    }

    public function toArray(): array
    {
        return [];
    }

    public function path(string $append = ''): string
    {
        if ($this->basePath === null) {
            return $append;
        }

        return $append === '' ? $this->basePath : $this->basePath . '/' . $append;
    }

    public function srcPath(string $append = ''): string
    {
        return $append;
    }

    public function appPath(string $append = ''): string
    {
        return $append;
    }

    public function configPath(string $append = ''): string
    {
        return $append;
    }

    public function databasePath(string $append = ''): string
    {
        return $append;
    }

    public function migrationPath(string $append = ''): string
    {
        return $append;
    }

    public function factoryPath(string $append = ''): string
    {
        return $append;
    }

    public function seederPath(string $append = ''): string
    {
        return $append;
    }

    public function langPath(string $append = ''): string
    {
        return $append;
    }

    public function resourcePath(string $append = ''): string
    {
        if ($this->basePath === null) {
            return $append;
        }

        return $this->path('resources' . ($append === '' ? '' : '/' . $append));
    }

    public function viewPath(string $append = ''): string
    {
        return $append;
    }

    public function routePath(string $append = ''): string
    {
        return $append;
    }

    public function publicPath(string $append = ''): string
    {
        return $append;
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
}
