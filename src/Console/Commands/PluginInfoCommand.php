<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;

/**
 * Displays detailed information about a single plugin.
 */
final class PluginInfoCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:info {alias : The plugin alias}';

    protected $description = 'Display detailed information about a plugin';

    public function handle(PluginManagerInterface $manager, PluginRecordRepositoryInterface $records): int
    {
        $plugin = $this->resolvePlugin($manager, (string) $this->argument('alias'));

        if ($plugin === null) {
            return self::FAILURE;
        }

        $record = $records->findByAlias($plugin->alias());

        $this->components->twoColumnDetail('Name', $plugin->name());
        $this->components->twoColumnDetail('Alias', $plugin->alias());
        $this->components->twoColumnDetail('Description', $plugin->description() ?: '—');
        $this->components->twoColumnDetail('Version', $plugin->version());
        $this->components->twoColumnDetail('Namespace', $plugin->namespace());
        $this->components->twoColumnDetail('Provider', $plugin->provider());
        $this->components->twoColumnDetail('Priority', (string) $plugin->priority());
        $this->components->twoColumnDetail('Enabled', $plugin->enabled() ? 'yes' : 'no');
        $this->components->twoColumnDetail('Path', $plugin->path());
        $this->components->twoColumnDetail('Requires', $this->summarize($plugin->requires()));
        $this->components->twoColumnDetail('Conflicts', $this->summarize($plugin->conflicts()));
        $this->components->twoColumnDetail('Permissions', $this->summarize($plugin->permissions()));
        $this->components->twoColumnDetail('Authors', $this->summarize($plugin->authors()));

        if ($record !== null) {
            $this->components->twoColumnDetail('Installed', (string) $record->installed_at);
            $this->components->twoColumnDetail('UUID', $record->uuid);
        } else {
            $this->components->twoColumnDetail('Installed', 'no');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int, mixed> $items
     */
    private function summarize(array $items): string
    {
        if ($items === []) {
            return '—';
        }

        return collect($items)
            ->map(static fn (mixed $item): string => is_array($item) ? json_encode($item) : (string) $item)
            ->implode(', ');
    }
}
