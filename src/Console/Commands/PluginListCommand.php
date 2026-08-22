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
            $rows[] = [
                $plugin->alias(),
                $plugin->name(),
                $plugin->version(),
                (string) $plugin->priority(),
                $plugin->enabled() ? 'yes' : 'no',
                $records->exists($plugin->alias()) ? 'yes' : 'no',
            ];
        }

        $this->table(['Alias', 'Name', 'Version', 'Priority', 'Enabled', 'Installed'], $rows);

        return self::SUCCESS;
    }
}
