<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a widget identifier cannot be found in the widget registry.
 */
final class DashboardWidgetNotFoundException extends RuntimeException
{
    public static function forId(string $id): self
    {
        return new self("No dashboard widget registered with the id [{$id}].");
    }
}
