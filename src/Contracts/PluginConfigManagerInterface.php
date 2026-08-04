<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Merges a plugin's "src/config" files into the application's config
 * repository. "config.php" is merged directly under the plugin's alias
 * key; any other file is merged under "{alias}.{filename}".
 */
interface PluginConfigManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
