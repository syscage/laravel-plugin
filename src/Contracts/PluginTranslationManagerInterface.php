<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Registers a plugin's "lang" directory under a translation namespace
 * matching its alias, e.g. `__('my-plugin::messages.welcome')`.
 */
interface PluginTranslationManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
