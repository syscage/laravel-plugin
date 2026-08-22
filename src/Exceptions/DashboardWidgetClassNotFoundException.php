<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin's manifest "widgets" entry points at a class that
 * does not exist or does not implement the expected contract.
 */
final class DashboardWidgetClassNotFoundException extends RuntimeException
{
    public static function forClass(string $class, string $pluginAlias): self
    {
        return new self(
            "The widget class [{$class}] declared by plugin [{$pluginAlias}] could not be found.",
        );
    }

    public static function invalidType(string $class, string $pluginAlias): self
    {
        return new self(
            "The widget class [{$class}] declared by plugin [{$pluginAlias}] must implement "
            . 'Syscage\\Plugin\\Contracts\\DashboardWidgetInterface.',
        );
    }
}
