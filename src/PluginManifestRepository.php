<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginManifestInterface;
use Syscage\Plugin\Contracts\PluginManifestRepositoryInterface;
use Syscage\Plugin\Exceptions\InvalidPluginManifestException;
use Syscage\Plugin\Support\PluginManifest;

/**
 * Filesystem-backed reader/writer for "plugin.json" manifest files.
 */
final class PluginManifestRepository implements PluginManifestRepositoryInterface
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function exists(string $path): bool
    {
        return $this->files->isFile($path);
    }

    public function read(string $path): PluginManifestInterface
    {
        if (! $this->exists($path)) {
            throw InvalidPluginManifestException::notFound($path);
        }

        $contents = $this->files->get($path);

        try {
            $data = json_decode($contents, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw InvalidPluginManifestException::malformedJson($path, $exception->getMessage());
        }

        return PluginManifest::fromArray((array) $data, $path);
    }

    public function write(string $path, PluginManifestInterface $manifest): void
    {
        $json = json_encode(
            $manifest->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        $this->files->put($path, $json . PHP_EOL);
    }
}
