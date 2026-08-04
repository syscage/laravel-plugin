<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin alias cannot be found in the registry.
 */
final class PluginNotFoundException extends RuntimeException
{
    public static function forAlias(string $alias): self
    {
        return new self("No plugin registered with the alias [{$alias}].");
    }

    public static function forProvider(string $class): self
    {
        return new self(
            "No registered plugin declares [{$class}] as its \"provider\". Ensure the plugin's "
            . '"plugin.json" "provider" field exactly matches this service provider\'s class name.',
        );
    }
}
