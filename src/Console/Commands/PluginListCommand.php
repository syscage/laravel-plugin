<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;

/**
 * Lists every discovered plugin and its current state.
 */
final class PluginListCommand extends Command
{
    protected $signature = 'plugin:list';

    protected $description = 'List every discovered plugin';

    public function handle(PluginManagerInterface $manager, PluginRecordRepositoryInterface $records): int
    {
        $plugins = $manager->all();

        if ($plugins === []) {
            $this->components->info('No plugins have been discovered.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($plugins as $plugin) {
            $record = $records->findByAlias($plugin->alias());

            $rows[] = [
                $plugin->alias(),
                $plugin->name(),
                $plugin->version(),
                $record?->enabled ? 'yes' : 'no',
                $record !== null ? 'yes' : 'no',
            ];
        }

        $this->table(['Alias', 'Name', 'Version', 'Enabled', 'Installed'], $rows);

        return self::SUCCESS;
    }
}
