<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesPlugin;
use Syscage\Plugin\Contracts\PluginAssetManagerInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginManagerInterface;

/**
 * Publishes a plugin's public assets into the host application's public
 * directory. Publishes every enabled plugin when no alias is given.
 */
final class PluginPublishCommand extends Command
{
    use ResolvesPlugin;

    protected $signature = 'plugin:publish {alias? : The plugin alias; omit to publish every enabled plugin}';

    protected $description = "Publish a plugin's public assets";

    public function handle(PluginManagerInterface $manager, PluginAssetManagerInterface $assets): int
    {
        $alias = $this->argument('alias');

        if ($alias !== null) {
            $plugin = $this->resolvePlugin($manager, (string) $alias);

            if ($plugin === null) {
                return self::FAILURE;
            }

            $this->publish($assets, $plugin);

            return self::SUCCESS;
        }

        foreach ($manager->enabled() as $plugin) {
            $this->publish($assets, $plugin);
        }

        return self::SUCCESS;
    }

    private function publish(PluginAssetManagerInterface $assets, PluginInterface $plugin): void
    {
        $assets->register($plugin);

        $this->components->twoColumnDetail($plugin->alias(), 'published');
    }
}
