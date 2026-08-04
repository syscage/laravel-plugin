<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Registers PSR-4 namespace mappings for plugin source directories at
 * runtime, without requiring a "composer dump-autoload".
 */
interface PluginAutoloaderInterface
{
    /**
     * Register a namespace-to-directory mapping so classes under the given
     * namespace can be autoloaded from the given directory.
     */
    public function register(string $namespace, string $path): void;

    /**
     * Determine whether the given namespace has already been registered.
     */
    public function isRegistered(string $namespace): bool;
}
