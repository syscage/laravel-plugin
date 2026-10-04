<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Output\OutputInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginSeederManagerInterface;

/**
 * Default implementation of {@see PluginSeederManagerInterface}.
 *
 * Delegates each plugin to Laravel's own "db:seed" command so seeding
 * behaves exactly like the host application's.
 */
final class PluginSeederManager implements PluginSeederManagerInterface
{
    public function __construct(
        private readonly PluginManagerInterface $plugins,
        private readonly Kernel $artisan,
    ) {
    }

    public function seedEnabled(?string $database = null, ?OutputInterface $output = null): array
    {
        $seeded = [];

        foreach ($this->plugins->enabled() as $plugin) {
            $class = rtrim($plugin->namespace(), '\\') . '\\Database\\Seeders\\DatabaseSeeder';

            if (! class_exists($class)) {
                continue;
            }

            $parameters = ['--class' => $class, '--force' => true];

            if ($database !== null) {
                $parameters['--database'] = $database;
            }

            $this->artisan->call('db:seed', $parameters, $output);

            $seeded[] = $plugin->alias();
        }

        return $seeded;
    }
}
