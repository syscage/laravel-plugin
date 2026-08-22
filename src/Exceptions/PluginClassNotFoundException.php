<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin's manifest points at a "Plugin" class that does not
 * exist or does not implement the expected contract.
 */
final class PluginClassNotFoundException extends RuntimeException
{
    public static function forClass(string $class, string $alias): self
    {
        return new self(
            "The plugin class [{$class}] for plugin [{$alias}] could not be found. "
            . 'Every plugin must contain a "src/Plugin.php" extending Syscage\\Plugin\\AbstractPlugin.',
        );
    }

    public static function invalidType(string $class, string $alias): self
    {
        return new self(
            "The plugin class [{$class}] for plugin [{$alias}] must implement "
            . 'Syscage\\Plugin\\Contracts\\PluginInterface.',
        );
    }
}
