<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\Support\PluginHasher;
use Throwable;

/**
 * Runs a set of health checks against one or every discovered plugin:
 * provider class presence, dependency/conflict validity, installation
 * state, and manifest drift since the last install/update.
 */
final class PluginDoctorCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:doctor {alias? : The plugin alias; omit to check every plugin}';

    protected $description = 'Diagnose problems with one or every plugin';

    public function handle(
        PluginManagerInterface $manager,
        PluginDependencyResolverInterface $resolver,
        PluginRecordRepositoryInterface $records,
    ): int {
        $alias = $this->argument('alias');

        if ($alias !== null) {
            $plugin = $this->resolvePlugin($manager, (string) $alias);

            if ($plugin === null) {
                return self::FAILURE;
            }

            $plugins = [$plugin->alias() => $plugin];
        } else {
            $plugins = $manager->all();
        }

        $rows = [];
        $healthy = true;

        foreach ($plugins as $plugin) {
            $checks = $this->diagnose($plugin, $manager, $resolver, $records);
            $healthy = $healthy && $checks['healthy'];

            $rows[] = [
                $plugin->alias(),
                $checks['provider'],
                $checks['dependencies'],
                $checks['installed'],
                $checks['drift'],
            ];
        }

        $this->table(['Alias', 'Provider', 'Dependencies', 'Installed', 'Manifest'], $rows);

        if (! $healthy) {
            $this->components->error('One or more plugins failed a health check.');

            return self::FAILURE;
        }

        $this->components->info('All checked plugins are healthy.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, PluginInterface> $plugins
     *
     * @return array{healthy: bool, provider: string, dependencies: string, installed: string, drift: string}
     */
    private function diagnose(
        PluginInterface $plugin,
        PluginManagerInterface $manager,
        PluginDependencyResolverInterface $resolver,
        PluginRecordRepositoryInterface $records,
    ): array {
        $healthy = true;

        if (class_exists($plugin->provider())) {
            $provider = '<fg=green>ok</>';
        } else {
            $provider = '<fg=red>missing</>';
            $healthy = false;
        }

        try {
            $resolver->validate($plugin, $manager->all());
            $dependencies = '<fg=green>ok</>';
        } catch (MissingPluginDependencyException|ConflictingPluginException $exception) {
            $dependencies = '<fg=red>' . $exception->getMessage() . '</>';
            $healthy = false;
        }

        $record = $records->findByAlias($plugin->alias());
        $installed = $record !== null ? '<fg=green>yes</>' : '<fg=yellow>no</>';

        if ($record === null) {
            $drift = 'n/a';
        } else {
            try {
                $drift = $record->hash === PluginHasher::hash($plugin)
                    ? '<fg=green>in sync</>'
                    : '<fg=yellow>drifted</>';
            } catch (Throwable) {
                $drift = '<fg=red>unreadable</>';
                $healthy = false;
            }
        }

        return [
            'healthy' => $healthy,
            'provider' => $provider,
            'dependencies' => $dependencies,
            'installed' => $installed,
            'drift' => $drift,
        ];
    }
}
