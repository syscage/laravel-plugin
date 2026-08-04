<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Registers a plugin's "resources/views" directory under a view namespace
 * matching its alias, e.g. `view('my-plugin::dashboard')`.
 */
interface PluginViewManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
