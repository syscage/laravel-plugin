<?php

declare(strict_types=1);

namespace Syscage\Plugin\Support;

use Syscage\Plugin\Contracts\PluginManifestInterface;
use Syscage\Plugin\Exceptions\InvalidPluginManifestException;

/**
 * Immutable value object representing the data stored in a plugin's
 * "plugin.json" manifest.
 */
final class PluginManifest implements PluginManifestInterface
{
    /**
     * The manifest fields that must be present in every "plugin.json".
     *
     * @var array<int, string>
     */
    private const REQUIRED_FIELDS = ['name', 'alias', 'version', 'provider', 'namespace'];

    private function __construct(
        private readonly ?string $id,
        private readonly string $name,
        private readonly string $alias,
        private readonly string $description,
        private readonly string $version,
        private readonly string $provider,
        private readonly string $namespace,
        private readonly int $priority,
        private readonly bool $enabled,
        private readonly array $requires,
        private readonly array $conflicts,
        private readonly array $permissions,
        private readonly array $sidebar,
        private readonly array $authors,
        private readonly ?string $tablePrefix,
    ) {
    }

    /**
     * Build a manifest from a decoded "plugin.json" array.
     *
     * @param array<string, mixed> $data
     *
     * @throws InvalidPluginManifestException
     */
    public static function fromArray(array $data, string $source = 'plugin.json'): self
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                throw InvalidPluginManifestException::missingField($source, $field);
            }
        }

        return new self(
            id: isset($data['id']) && $data['id'] !== '' ? (string) $data['id'] : null,
            name: (string) $data['name'],
            alias: (string) $data['alias'],
            description: (string) ($data['description'] ?? ''),
            version: (string) $data['version'],
            provider: (string) $data['provider'],
            namespace: (string) $data['namespace'],
            priority: (int) ($data['priority'] ?? 100),
            enabled: (bool) ($data['enabled'] ?? true),
            requires: (array) ($data['requires'] ?? []),
            conflicts: (array) ($data['conflicts'] ?? []),
            permissions: (array) ($data['permissions'] ?? []),
            sidebar: (array) ($data['sidebar'] ?? []),
            authors: (array) ($data['authors'] ?? []),
            tablePrefix: isset($data['table_prefix']) && $data['table_prefix'] !== ''
                ? (string) $data['table_prefix']
                : null,
        );
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function namespace(): string
    {
        return $this->namespace;
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
        return $this->permissions;
    }

    public function sidebar(): array
    {
        return $this->sidebar;
    }

    public function authors(): array
    {
        return $this->authors;
    }

    public function tablePrefix(): ?string
    {
        return $this->tablePrefix;
    }

    /**
     * Return a copy of this manifest with a newly assigned identifier.
     */
    public function withId(string $id): self
    {
        return $this->with(id: $id);
    }

    /**
     * Return a copy of this manifest with a different enabled state.
     */
    public function withEnabled(bool $enabled): self
    {
        return $this->with(enabled: $enabled);
    }

    /**
     * Return a copy of this manifest with a different version.
     */
    public function withVersion(string $version): self
    {
        return $this->with(version: $version);
    }

    private function with(
        ?string $id = null,
        ?bool $enabled = null,
        ?string $version = null,
    ): self {
        return new self(
            id: $id ?? $this->id,
            name: $this->name,
            alias: $this->alias,
            description: $this->description,
            version: $version ?? $this->version,
            provider: $this->provider,
            namespace: $this->namespace,
            priority: $this->priority,
            enabled: $enabled ?? $this->enabled,
            requires: $this->requires,
            conflicts: $this->conflicts,
            permissions: $this->permissions,
            sidebar: $this->sidebar,
            authors: $this->authors,
            tablePrefix: $this->tablePrefix,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'description' => $this->description,
            'version' => $this->version,
            'provider' => $this->provider,
            'namespace' => $this->namespace,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
            'requires' => $this->requires,
            'conflicts' => $this->conflicts,
            'permissions' => $this->permissions,
            'sidebar' => $this->sidebar,
            'authors' => $this->authors,
            'table_prefix' => $this->tablePrefix,
        ];
    }
}
