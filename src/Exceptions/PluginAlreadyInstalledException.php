<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when attempting to install a plugin that already has a database
 * record.
 */
final class PluginAlreadyInstalledException extends RuntimeException
{
    public static function forAlias(string $alias): self
    {
        return new self("Plugin [{$alias}] is already installed.");
    }
}
