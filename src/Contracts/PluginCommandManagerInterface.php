<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Discovers and registers a plugin's console commands, found under
 * "src/app/Console/Commands".
 */
interface PluginCommandManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
