<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesDirectly;

/**
 * Delegates to "config:cache". Plugin config is merged into the
 * application config automatically during normal boot, so caching the
 * configuration always includes every enabled plugin's contribution; the
 * "plugin" argument only validates that the given alias exists.
 */
final class ConfigCachePluginCommand extends Command
{
    use DelegatesDirectly;

    protected $signature = 'config:cache-plugin {plugin : Validated for command-family consistency; has no effect on scope}';

    protected $description = 'Create a cache file for faster configuration loading (identical to "config:cache")';

    protected function delegateCommand(): string
    {
        return 'config:cache';
    }
}
