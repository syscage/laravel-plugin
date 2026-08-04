<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when an operation requires a plugin to already be installed, but
 * no database record for it exists.
 */
final class PluginNotInstalledException extends RuntimeException
{
    public static function forAlias(string $alias): self
    {
        return new self("Plugin [{$alias}] is not installed.");
    }
}
