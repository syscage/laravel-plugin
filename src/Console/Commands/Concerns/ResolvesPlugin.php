<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;

/**
 * Resolves a plugin by alias for a console command, printing a consistent
 * error message when it cannot be found.
 */
trait ResolvesPlugin
{
    protected function resolvePlugin(PluginManagerInterface $manager, string $alias): ?PluginInterface
    {
        try {
            return $manager->find($alias);
        } catch (PluginNotFoundException) {
            $this->components->error("No plugin registered with the alias [{$alias}].");

            return null;
        }
    }
}
