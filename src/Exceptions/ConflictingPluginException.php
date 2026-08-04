<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when two plugins that declare each other as conflicts are both
 * present in the same resolution set.
 */
final class ConflictingPluginException extends RuntimeException
{
    public static function between(string $alias, string $conflictingAlias): self
    {
        return new self("Plugin [{$alias}] conflicts with [{$conflictingAlias}]. Both cannot be enabled at once.");
    }
}
