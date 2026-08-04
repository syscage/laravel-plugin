<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Delegates to "vendor:publish", scoped to the given plugin's own service
 * provider via "--provider".
 */
final class VendorPublishPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'vendor:publish-plugin
        {plugin : The plugin whose publishable resources should be published}
        {--existing : Publish and overwrite only the files that have already been published}
        {--force : Overwrite any existing files}
        {--tag=* : One or many tags that have assets you want to publish}';

    protected $description = "Publish a plugin's publishable resources";

    public function handle(): int
    {
        $plugin = $this->targetPlugin();

        return $this->call('vendor:publish', array_filter([
            '--provider' => $plugin->provider(),
            '--existing' => $this->option('existing'),
            '--force' => $this->option('force'),
            '--tag' => $this->option('tag'),
        ], static fn (mixed $value): bool => $value !== null && $value !== false && $value !== []));
    }
}
