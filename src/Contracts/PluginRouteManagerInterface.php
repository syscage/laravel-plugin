<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Registers a plugin's "routes/web.php", "routes/api.php", and
 * "routes/console.php" files, when present.
 */
interface PluginRouteManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
