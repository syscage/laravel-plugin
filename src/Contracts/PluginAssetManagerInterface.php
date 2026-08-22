<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Publishes a plugin's "src/public" assets so they are reachable from the
 * host application's public directory.
 */
interface PluginAssetManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
