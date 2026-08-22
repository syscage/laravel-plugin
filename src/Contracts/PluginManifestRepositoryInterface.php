<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\InvalidPluginManifestException;

/**
 * Reads and persists a plugin's "plugin.json" manifest file.
 */
interface PluginManifestRepositoryInterface
{
    /**
     * Determine whether a manifest file exists at the given path.
     */
    public function exists(string $path): bool;

    /**
     * Read and parse the manifest file at the given path.
     *
     * @throws InvalidPluginManifestException
     */
    public function read(string $path): PluginManifestInterface;

    /**
     * Persist the given manifest to the given path.
     */
    public function write(string $path, PluginManifestInterface $manifest): void;
}
