<?php

declare(strict_types=1);

namespace Syscage\Plugin\Exceptions;

use RuntimeException;

/**
 * Thrown when a plugin requires another plugin that is not installed,
 * not enabled, or does not satisfy the required version constraint.
 */
final class MissingPluginDependencyException extends RuntimeException
{
    public static function notPresent(string $alias, string $requiredAlias): self
    {
        return new self(
            "Plugin [{$alias}] requires [{$requiredAlias}], which is not installed or not enabled.",
        );
    }

    public static function versionMismatch(string $alias, string $requiredAlias, string $constraint, string $actual): self
    {
        return new self(
            "Plugin [{$alias}] requires [{$requiredAlias}:{$constraint}], but the installed version is [{$actual}].",
        );
    }
}
