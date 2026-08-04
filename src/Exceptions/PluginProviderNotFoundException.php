<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin's manifest points at a service provider class that
 * does not exist.
 */
final class PluginProviderNotFoundException extends RuntimeException
{
    public static function forClass(string $class, string $alias): self
    {
        return new self("The service provider [{$class}] declared by plugin [{$alias}] could not be found.");
    }
}
