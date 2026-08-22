<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when the "requires" graph of the resolved plugins contains a
 * cycle, making a valid loading order impossible.
 */
final class CircularPluginDependencyException extends RuntimeException
{
    /**
     * @param array<int, string> $cycle
     */
    public static function forCycle(array $cycle): self
    {
        return new self('A circular plugin dependency was detected: ' . implode(' -> ', $cycle) . '.');
    }
}
