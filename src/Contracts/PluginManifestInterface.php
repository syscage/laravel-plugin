<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Read-only access to the data stored in a plugin's "plugin.json" manifest.
 */
interface PluginManifestInterface
{
    /**
     * The plugin's unique identifier, or null until `plugin:install` assigns one.
     */
    public function id(): ?string;

    /**
     * The human-readable plugin name.
     */
    public function name(): string;

    /**
     * The unique machine-readable alias used for folder names, routing, and commands.
     */
    public function alias(): string;

    /**
     * The short description of the plugin's purpose.
     */
    public function description(): string;

    /**
     * The current plugin version, following Semantic Versioning.
     */
    public function version(): string;

    /**
     * The fully qualified class name of the plugin's primary service provider.
     */
    public function provider(): string;

    /**
     * The root PHP namespace of the plugin.
     */
    public function namespace(): string;

    /**
     * The plugin's loading priority. Lower values load first.
     */
    public function priority(): int;

    /**
     * Whether the plugin should be loaded by the framework.
     */
    public function enabled(): bool;

    /**
     * The plugins (and optional version constraints) this plugin requires.
     *
     * @return array<int, array<string, mixed>>
     */
    public function requires(): array;

    /**
     * The plugin aliases that cannot be enabled at the same time as this plugin.
     *
     * @return array<int, string>
     */
    public function conflicts(): array;

    /**
     * The permissions automatically registered during installation.
     *
     * @return array<int, string>
     */
    public function permissions(): array;

    /**
     * The navigation items this plugin contributes to the merged sidebar manifest.
     *
     * @return array<string, mixed>
     */
    public function sidebar(): array;

    /**
     * The plugin's authors and maintainers.
     *
     * @return array<int, array<string, mixed>>
     */
    public function authors(): array;

    /**
     * The table-name prefix this plugin's generators should use, or null to
     * fall back to the framework's globally configured default.
     */
    public function tablePrefix(): ?string;

    /**
     * The manifest data in its original array form.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
