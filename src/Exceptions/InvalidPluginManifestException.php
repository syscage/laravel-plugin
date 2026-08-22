<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a "plugin.json" manifest cannot be parsed or is missing a
 * required field.
 */
final class InvalidPluginManifestException extends RuntimeException
{
    public static function malformedJson(string $path, string $error): self
    {
        return new self("The plugin manifest at [{$path}] contains invalid JSON: {$error}");
    }

    public static function missingField(string $path, string $field): self
    {
        return new self("The plugin manifest at [{$path}] is missing the required field [{$field}].");
    }

    public static function notFound(string $path): self
    {
        return new self("No plugin manifest could be found at [{$path}].");
    }
}
